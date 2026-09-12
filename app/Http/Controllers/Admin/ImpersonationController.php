<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    private const SESSION_IMPERSONATOR_ID = 'impersonator_id';
    private const SESSION_IMPERSONATOR_NAME = 'impersonator_name';

    public function start(User $user, Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $admin = $request->user();

        if (!$admin || $admin->role !== User::ROLE_ADMIN) {
            abort(403);
        }

        if ($admin->is($user)) {
            return back()->with('status', 'Tidak bisa impersonate akun sendiri.');
        }

        if ($request->session()->has(self::SESSION_IMPERSONATOR_ID)) {
            return back()->with('status', 'Selesaikan sesi impersonate yang aktif terlebih dahulu.');
        }

        $request->session()->put(self::SESSION_IMPERSONATOR_ID, $admin->id);
        $request->session()->put(self::SESSION_IMPERSONATOR_NAME, $admin->name);

        Auth::login($user);
        $request->session()->regenerate();

        $auditLog->log(
            $admin,
            'user.impersonate.start',
            $user,
            ['impersonated_user_id' => $user->id],
            $request
        );

        return redirect()->route($this->defaultRouteForRole($user->role))
            ->with('status', 'Impersonate aktif. Anda sedang masuk sebagai '.$user->name.'.');
    }

    public function stop(Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $impersonatedUser = $request->user();
        $impersonatorId = $request->session()->get(self::SESSION_IMPERSONATOR_ID);

        if (!$impersonatorId) {
            abort(403);
        }

        $impersonator = User::query()->find($impersonatorId);

        $request->session()->forget([
            self::SESSION_IMPERSONATOR_ID,
            self::SESSION_IMPERSONATOR_NAME,
        ]);

        if (!$impersonator || $impersonator->role !== User::ROLE_ADMIN) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', 'Sesi impersonate tidak valid. Silakan login ulang.');
        }

        Auth::login($impersonator);
        $request->session()->regenerate();

        $auditLog->log(
            $impersonator,
            'user.impersonate.stop',
            $impersonatedUser,
            ['impersonated_user_id' => $impersonatedUser?->id],
            $request
        );

        return redirect()->route('admin.users.index')
            ->with('status', 'Kembali ke akun admin.');
    }

    private function defaultRouteForRole(string $role): string
    {
        return match ($role) {
            User::ROLE_KAMPUS => 'kampus.lulusan.index',
            User::ROLE_LEMBAGA => 'lembaga.dashboard',
            User::ROLE_REGULATOR => 'regulator.analytics.index',
            User::ROLE_MITRA,
            User::ROLE_FASKES,
            User::ROLE_HEADHUNTER => 'mitra.kandidat.index',
            User::ROLE_PERAWAT => 'perawat.dashboard',
            User::ROLE_ADMIN => 'admin.dashboard',
            default => 'dashboard',
        };
    }
}
