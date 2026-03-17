<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'ip',
        'user_agent',
    ];

    /**
     * The user who performed the action (nullable for system/anonymous).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an audit event. Do not store PHI in logs—use type/id only.
     */
    public static function log(string $action, ?string $auditableType = null, ?int $auditableId = null): self
    {
        return self::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'auditable_type' => $auditableType ?? '',
            'auditable_id' => $auditableId,
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
