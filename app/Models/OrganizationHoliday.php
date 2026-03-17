<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationHoliday extends Model
{
    protected $table = 'organization_holidays';

    protected $fillable = [
        'organization_id',
        'date',
        'name',
        'recurring',
    ];

    protected $casts = [
        'date' => 'date',
        'recurring' => 'boolean',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
