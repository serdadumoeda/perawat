<?php

namespace App\Http\Controllers\Regulator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Regulator\ReopenLegalitasDataRequest;
use App\Http\Requests\Regulator\ReviewLegalitasDataRequest;
use App\Models\DataLegalitas;
use App\Models\User;
use App\Notifications\LegalitasDataStatusNotification;
use App\Services\AuditLogService;
use App\Services\RegulatorOrganisasiResolver;
use App\Services\VerificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class LegalitasDataController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DataLegalitas::class);

        $status = $request->query('status', DataLegalitas::DATA_STATUS_SUBMITTED);
        $search = (string) $request->string('q');

        $query = DataLegalitas::with(['perawat', 'dataReviewedBy', 'regulatorOrganisasi']);
        $this->applyRegulatorScope($query, $request->user());

        if ($search !== '') {
            $query->whereHas('perawat', function (Builder $q) use ($search) {
                $term = '%'.$search.'%';
                $q->where('nama', 'like', $term)
                    ->orWhere('nik', 'like', $term);
            });
        }

        if ($status !== 'all') {
            $query->where('data_status', $status);
        }

        $records = $query->latest()->paginate(20)->withQueryString();

        return view('regulator.legalitas.pending_data', [
            'records' => $records,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function review(
        ReviewLegalitasDataRequest $request,
        DataLegalitas $legalitas,
        AuditLogService $auditLog,
        VerificationService $verification,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse
    {
        $this->authorize('review', $legalitas);

        if (!in_array($legalitas->data_status, [
            DataLegalitas::DATA_STATUS_SUBMITTED,
            DataLegalitas::DATA_STATUS_PENDING_SYNC,
        ], true)) {
            return back()->withErrors([
                'status' => 'Data legalitas sudah final dan tidak bisa direview ulang.',
            ]);
        }

        $action = $request->validated('action');
        $note = $request->validated('note');
        $regulatorContext = $organisasiResolver->context($request->user());

        $targetStatus = $action === VerificationService::ACTION_APPROVE
            ? ($legalitas->status_str === DataLegalitas::STATUS_STR_EXPIRED
                ? DataLegalitas::DATA_STATUS_EXPIRED
                : DataLegalitas::DATA_STATUS_VERIFIED)
            : DataLegalitas::DATA_STATUS_REJECTED;

        $context = [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'note' => $note,
            'extra' => [
                'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            ],
        ];

        $legalitas = $verification->apply($legalitas, $action, [
            'status_field' => 'data_status',
            'approved_value' => $targetStatus,
            'rejected_value' => DataLegalitas::DATA_STATUS_REJECTED,
            'reviewed_by_field' => 'data_reviewed_by',
            'reviewed_at_field' => 'data_reviewed_at',
            'note_field' => 'data_rejection_note',
        ], $context);

        $auditLog->log($request->user(), 'legalitas.data.review', $legalitas, [
            'status' => $targetStatus,
            'note' => $note,
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
            'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            'regulator_organisasi_nama' => $regulatorContext['regulator_organisasi_nama'],
            'regulator_organisasi_kode' => $regulatorContext['regulator_organisasi_kode'],
        ], $request);

        $this->notifyPerawat($legalitas, $note);

        return back()->with('status', 'Review data legalitas berhasil disimpan.');
    }

    public function reopen(
        ReopenLegalitasDataRequest $request,
        DataLegalitas $legalitas,
        AuditLogService $auditLog,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse {
        $this->authorize('review', $legalitas);

        $finalStatuses = [
            DataLegalitas::DATA_STATUS_VERIFIED,
            DataLegalitas::DATA_STATUS_REJECTED,
            DataLegalitas::DATA_STATUS_EXPIRED,
        ];

        if (!in_array($legalitas->data_status, $finalStatuses, true)) {
            return back()->withErrors([
                'status' => 'Data legalitas belum status final sehingga tidak perlu reopen.',
            ]);
        }

        $previousStatus = $legalitas->data_status;
        $legalitas->fill([
            'data_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'data_reviewed_by' => null,
            'data_reviewed_at' => null,
            'data_rejection_note' => null,
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
        ])->save();

        $regulatorContext = $organisasiResolver->context($request->user());
        $note = $request->validated('note');

        $auditLog->log($request->user(), 'legalitas.data.reopen', $legalitas, [
            'previous_status' => $previousStatus,
            'next_status' => DataLegalitas::DATA_STATUS_SUBMITTED,
            'note' => $note,
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
            'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            'regulator_organisasi_nama' => $regulatorContext['regulator_organisasi_nama'],
            'regulator_organisasi_kode' => $regulatorContext['regulator_organisasi_kode'],
        ], $request);

        $this->notifyPerawat($legalitas, $note);

        return back()->with('status', 'Data legalitas berhasil di-reopen ke status submitted.');
    }

    private function notifyPerawat(DataLegalitas $legalitas, ?string $note): void
    {
        $user = User::where('role', User::ROLE_PERAWAT)
            ->where('perawat_nik', $legalitas->perawat_nik)
            ->first();

        if (!$user) {
            return;
        }

        $status = $legalitas->data_status ?? DataLegalitas::DATA_STATUS_DRAFT;
        $message = match ($status) {
            DataLegalitas::DATA_STATUS_VERIFIED => 'Data STR Anda telah diverifikasi.',
            DataLegalitas::DATA_STATUS_EXPIRED => 'Status STR Anda dinyatakan expired.',
            DataLegalitas::DATA_STATUS_REJECTED => 'Data STR Anda ditolak.',
            default => 'Status data STR Anda diperbarui.',
        };

        Notification::send($user, new LegalitasDataStatusNotification($legalitas, $status, $note, $message));
    }

    private function applyRegulatorScope(Builder $query, User $user): void
    {
        $allowedOrgIds = $user->regulatorOrganisasi()
            ->where('regulator_organisasi.is_active', true)
            ->pluck('regulator_organisasi.id')
            ->all();

        $query->where(function (Builder $q) use ($allowedOrgIds) {
            $q->whereNull('regulator_organisasi_id');

            if ($allowedOrgIds !== []) {
                $q->orWhereIn('regulator_organisasi_id', $allowedOrgIds);
            }
        });
    }
}
