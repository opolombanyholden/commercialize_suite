<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Loggable
{
    protected static function bootLoggable(): void
    {
        static::created(function ($model) {
            $model->logActivity('created', 'Création');
        });

        static::updated(function ($model) {
            $model->logActivity('updated', 'Modification', $model->getChanges());
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', 'Suppression');
        });
    }

    public function logActivity(string $action, string $description = null, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'company_id' => $this->company_id ?? auth()->user()?->company_id,
            'user_id' => auth()->id(),
            'subject_type' => get_class($this),
            'subject_id' => $this->id,
            'action' => $action,
            'description' => $description ?? "Action: {$action}",
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function activityLogs()
    {
        return $this->morphMany(ActivityLog::class, 'subject')
            ->orderBy('created_at', 'desc');
    }

    public function recentActivity(int $limit = 10)
    {
        return $this->activityLogs()->limit($limit)->get();
    }

    public function logCustomActivity(string $action, string $description, array $data = []): ActivityLog
    {
        return $this->logActivity($action, $description, $data);
    }
}
