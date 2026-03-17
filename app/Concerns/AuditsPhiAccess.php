<?php

namespace App\Concerns;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait AuditsPhiAccess
{
    /**
     * Log access to or change of a PHI-related resource. Call from controllers/actions.
     * Do not pass PHI—only action and resource type/id.
     */
    protected function auditPhi(string $action, ?Model $model = null): void
    {
        AuditLog::log(
            $action,
            $model ? $model->getMorphClass() : null,
            $model?->getKey(),
        );
    }
}
