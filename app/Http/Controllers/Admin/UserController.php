<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->orderByDesc('created_at');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => User::ROLES,
            'statuses' => [User::STATUS_ACTIVE, User::STATUS_PENDING, User::STATUS_SUSPENDED],
        ]);
    }

    public function approve(User $user, AuditLogService $auditLog): RedirectResponse
    {
        $user->status = User::STATUS_ACTIVE;
        $user->approved_at = now();
        $user->suspended_at = null;
        $user->save();

        $auditLog->log(request()->user(), 'user.approve', $user, ['user_id' => $user->id], request());

        return back()->with('status', 'User berhasil disetujui.');
    }

    public function suspend(User $user, AuditLogService $auditLog): RedirectResponse
    {
        if (request()->user()->is($user)) {
            return back()->withErrors(['user' => 'Tidak bisa menangguhkan akun sendiri.']);
        }

        $user->status = User::STATUS_SUSPENDED;
        $user->suspended_at = now();
        $user->save();

        $auditLog->log(request()->user(), 'user.suspend', $user, ['user_id' => $user->id], request());

        return back()->with('status', 'User berhasil ditangguhkan.');
    }

    public function activate(User $user, AuditLogService $auditLog): RedirectResponse
    {
        $user->status = User::STATUS_ACTIVE;
        $user->approved_at = $user->approved_at ?? now();
        $user->suspended_at = null;
        $user->save();

        $auditLog->log(request()->user(), 'user.activate', $user, ['user_id' => $user->id], request());

        return back()->with('status', 'User berhasil diaktifkan.');
    }

    public function resetPassword(User $user, AuditLogService $auditLog): RedirectResponse
    {
        if (request()->user()->is($user)) {
            return back()->withErrors(['user' => 'Tidak bisa reset password akun sendiri dari sini.']);
        }

        Password::sendResetLink(['email' => $user->email]);

        $auditLog->log(request()->user(), 'user.reset_password', $user, ['user_id' => $user->id], request());

        return back()->with('status', 'Link reset password telah dikirim ke email pengguna.');
    }
}
