<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogService
{
    public function log(?User $user, string $action, ?Model $subject = null, array $meta = [], ?Request $request = null): AuditLog
    {
        $baseMeta = [
            'actor_name' => $user?->name,
            'actor_role' => $user?->role,
        ];

        $payload = [
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),
            'meta' => array_merge($baseMeta, $meta),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ];

        return AuditLog::create($payload);
    }
}
