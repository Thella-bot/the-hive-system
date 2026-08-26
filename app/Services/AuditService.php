<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    /**
     * Log an audit event.
     */
    public function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
    ): AuditLog {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log model creation.
     */
    public function logCreated(Model $model): AuditLog
    {
        return $this->log('created', model: $model, newValues: $model->getAttributes());
    }

    /**
     * Log model update.
     */
    public function logUpdated(Model $model, array $oldValues): AuditLog
    {
        return $this->log('updated', model: $model, oldValues: $oldValues, newValues: $model->getChanges());
    }

    /**
     * Log model deletion.
     */
    public function logDeleted(Model $model): AuditLog
    {
        return $this->log('deleted', model: $model, oldValues: $model->getOriginal());
    }

    /**
     * Log user login.
     */
    public function logLogin(): AuditLog
    {
        return $this->log('login');
    }

    /**
     * Log user logout.
     */
    public function logLogout(): AuditLog
    {
        return $this->log('logout');
    }
}
