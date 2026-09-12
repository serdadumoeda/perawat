<?php

namespace App\Http\Controllers\Regulator;

use App\Http\Controllers\Controller;
use App\Http\Requests\Regulator\ReopenLegalitasDocumentRequest;
use App\Http\Requests\Regulator\ReviewLegalitasDocumentRequest;
use App\Models\AuditLog;
use App\Models\DataLegalitas;
use App\Models\User;
use App\Notifications\LegalitasDocumentStatusNotification;
use App\Services\AuditLogService;
use App\Services\RegulatorOrganisasiResolver;
use App\Services\VerificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class LegalitasDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DataLegalitas::class);

        $docType = $request->query('doc_type', 'all');
        $status = $request->query('status', DataLegalitas::DOC_STATUS_PENDING);
        $search = (string) $request->string('q');

        $query = DataLegalitas::with([
            'perawat',
            'dataReviewedBy',
            'strDocumentReviewedBy',
            'izinLuarNegeriDocumentReviewedBy',
            'regulatorOrganisasi',
        ])
            ->where(function (Builder $q) {
                $q->whereNotNull('str_document_path')
                    ->orWhereNotNull('izin_luar_negeri_document_path');
            });
        $this->applyRegulatorScope($query, $request->user());

        if ($search !== '') {
            $query->whereHas('perawat', function (Builder $q) use ($search) {
                $term = '%'.$search.'%';
                $q->where('nama', 'like', $term)
                    ->orWhere('nik', 'like', $term);
            });
        }

        if ($docType === 'str') {
            $query->whereNotNull('str_document_path');
            $this->applyDocStatusFilter($query, 'str', $status);
        } elseif ($docType === 'izin_luar_negeri') {
            $query->whereNotNull('izin_luar_negeri_document_path');
            $this->applyDocStatusFilter($query, 'izin_luar_negeri', $status);
        } elseif ($status !== 'all') {
            $query->where(function (Builder $q) use ($status) {
                $q->where(function (Builder $sub) use ($status) {
                    $sub->whereNotNull('str_document_path');
                    $this->applyDocStatusFilter($sub, 'str', $status);
                })->orWhere(function (Builder $sub) use ($status) {
                    $sub->whereNotNull('izin_luar_negeri_document_path');
                    $this->applyDocStatusFilter($sub, 'izin_luar_negeri', $status);
                });
            });
        }

        $documents = $query->latest()->paginate(20)->withQueryString();

        return view('regulator.legalitas.documents', [
            'documents' => $documents,
            'docType' => $docType,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function review(
        ReviewLegalitasDocumentRequest $request,
        DataLegalitas $legalitas,
        AuditLogService $auditLog,
        VerificationService $verification,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse
    {
        $this->authorize('review', $legalitas);

        $docType = $request->validated('document_type');
        $action = $request->validated('action');
        $note = $request->validated('note');
        $regulatorContext = $organisasiResolver->context($request->user());

        $prefix = $docType === 'str' ? 'str_document' : 'izin_luar_negeri_document';
        $pathField = $prefix.'_path';
        if (!$legalitas->{$pathField}) {
            return back()->withErrors(['document_type' => 'Dokumen tidak ditemukan pada data legalitas ini.']);
        }

        $statusField = $prefix.'_status';
        $currentStatus = DataLegalitas::normalizeDocStatus($legalitas->{$statusField});
        if (in_array($currentStatus, [DataLegalitas::DOC_STATUS_APPROVED, DataLegalitas::DOC_STATUS_REJECTED], true)) {
            return back()->withErrors(['status' => 'Dokumen sudah final. Gunakan reopen untuk review ulang.']);
        }

        $status = $action === 'approve'
            ? DataLegalitas::DOC_STATUS_APPROVED
            : DataLegalitas::DOC_STATUS_REJECTED;
        $context = [
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'extra' => [
                'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            ],
        ];
        if ($request->filled('note')) {
            $context['note'] = $note;
        }

        $verification->apply($legalitas, $action, [
            'status_field' => $prefix.'_status',
            'approved_value' => DataLegalitas::DOC_STATUS_APPROVED,
            'rejected_value' => DataLegalitas::DOC_STATUS_REJECTED,
            'reviewed_by_field' => $prefix.'_reviewed_by',
            'reviewed_at_field' => $prefix.'_reviewed_at',
            'note_field' => $prefix.'_note',
        ], $context);

        $auditLog->log($request->user(), 'legalitas.document.review', $legalitas, [
            'document_type' => $docType,
            'status' => $status,
            'note' => $note,
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
            'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            'regulator_organisasi_nama' => $regulatorContext['regulator_organisasi_nama'],
            'regulator_organisasi_kode' => $regulatorContext['regulator_organisasi_kode'],
        ], $request);

        $this->notifyPerawat($legalitas, $docType, $status, $note);

        return back()->with('status', 'Review dokumen berhasil disimpan.');
    }

    public function reopen(
        ReopenLegalitasDocumentRequest $request,
        DataLegalitas $legalitas,
        AuditLogService $auditLog,
        RegulatorOrganisasiResolver $organisasiResolver
    ): RedirectResponse {
        $this->authorize('review', $legalitas);

        $docType = $request->validated('document_type');
        $note = $request->validated('note');
        $prefix = $docType === 'str' ? 'str_document' : 'izin_luar_negeri_document';
        $pathField = $prefix.'_path';
        $statusField = $prefix.'_status';
        $reviewedByField = $prefix.'_reviewed_by';
        $reviewedAtField = $prefix.'_reviewed_at';
        $noteField = $prefix.'_note';

        if (!$legalitas->{$pathField}) {
            return back()->withErrors(['document_type' => 'Dokumen tidak ditemukan pada data legalitas ini.']);
        }

        $currentStatus = DataLegalitas::normalizeDocStatus($legalitas->{$statusField});
        if (!in_array($currentStatus, [DataLegalitas::DOC_STATUS_APPROVED, DataLegalitas::DOC_STATUS_REJECTED], true)) {
            return back()->withErrors(['status' => 'Dokumen belum status final sehingga tidak perlu reopen.']);
        }

        $legalitas->fill([
            $statusField => DataLegalitas::DOC_STATUS_PENDING,
            $reviewedByField => null,
            $reviewedAtField => null,
            $noteField => null,
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
        ])->save();

        $regulatorContext = $organisasiResolver->context($request->user());

        $auditLog->log($request->user(), 'legalitas.document.reopen', $legalitas, [
            'document_type' => $docType,
            'previous_status' => $currentStatus,
            'next_status' => DataLegalitas::DOC_STATUS_PENDING,
            'note' => $note,
            'source_system' => 'regulator_portal',
            'source_actor' => 'regulator',
            'regulator_organisasi_id' => $regulatorContext['regulator_organisasi_id'],
            'regulator_organisasi_nama' => $regulatorContext['regulator_organisasi_nama'],
            'regulator_organisasi_kode' => $regulatorContext['regulator_organisasi_kode'],
        ], $request);

        $this->notifyPerawat(
            $legalitas,
            $docType,
            DataLegalitas::DOC_STATUS_PENDING,
            $note
        );

        return back()->with('status', 'Dokumen berhasil di-reopen ke status pending.');
    }

    public function audit(Request $request, DataLegalitas $legalitas): View
    {
        $this->authorize('view', $legalitas);

        $docType = $request->query('doc_type', 'all');

        $logsQuery = AuditLog::query()
            ->where('subject_type', DataLegalitas::class)
            ->where('subject_id', $legalitas->id);

        if ($docType !== 'all') {
            $logsQuery->where('meta->document_type', $docType);
        }

        $logs = $logsQuery->latest()->paginate(20)->withQueryString();

        $legalitas->loadMissing('perawat');

        return view('regulator.legalitas.documents_audit', [
            'legalitas' => $legalitas,
            'logs' => $logs,
            'docType' => $docType,
        ]);
    }

    private function applyDocStatusFilter(Builder $query, string $docType, string $status): void
    {
        if ($status === 'all') {
            return;
        }

        $column = $docType === 'str' ? 'str_document_status' : 'izin_luar_negeri_document_status';

        if ($status === DataLegalitas::DOC_STATUS_PENDING) {
            $query->where(function (Builder $q) use ($column) {
                $q->where($column, DataLegalitas::DOC_STATUS_PENDING)
                    ->orWhereNull($column);
            });
            return;
        }

        $query->where($column, $status);
    }

    private function notifyPerawat(DataLegalitas $legalitas, string $docType, string $status, ?string $note): void
    {
        $user = User::where('role', User::ROLE_PERAWAT)
            ->where('perawat_nik', $legalitas->perawat_nik)
            ->first();

        if (!$user) {
            return;
        }

        $label = $docType === 'str' ? 'STR' : 'Izin Luar Negeri';
        $message = match ($status) {
            DataLegalitas::DOC_STATUS_APPROVED => "Dokumen {$label} disetujui.",
            DataLegalitas::DOC_STATUS_PENDING => "Dokumen {$label} dikembalikan ke status pending review.",
            default => "Dokumen {$label} ditolak.",
        };

        Notification::send($user, new LegalitasDocumentStatusNotification($legalitas, $docType, $status, $note, $message));
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
