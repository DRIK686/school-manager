<?php
namespace App\Services;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Log a sensitive or auditable action.
     *
     * @param string $action       short machine-readable key, e.g. 'student.withdraw'
     * @param string $description  human-readable summary, e.g. "Withdrew Abla Cui"
     * @param mixed  $subject      the affected model (optional) — logs its class + id
     * @param \App\Models\User|null $actorOverride  pass a user explicitly for cases
     *        where auth()->user() isn't set yet (e.g. right after a fresh login)
     */
    public static function log(string $action, string $description, $subject = null, $actorOverride = null): void
    {
        $user = $actorOverride ?? auth()->user();
        try {
            ActivityLog::create([
                'user_id'      => $user?->id,
                'user_name'    => $user?->name ?? 'Guest',
                'user_role'    => $user?->role?->name ?? ($actorOverride ? 'Student' : null),
                'action'       => $action,
                'description'  => $description,
                'subject_type' => $subject ? get_class($subject) : null,
                'subject_id'   => $subject?->id,
                'ip_address'   => Request::ip(),
            ]);
        } catch (\Exception $e) {
            // Logging must never break the actual request
        }
    }
}
