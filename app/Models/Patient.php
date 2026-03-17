<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Patient extends Model
{
    /**
     * The user account for this patient.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Physicians this patient is associated with.
     */
    public function physicians(): BelongsToMany
    {
        return $this->belongsToMany(Physician::class, 'patient_physician');
    }
}
