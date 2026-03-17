<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class Organization extends Model
{
    use Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'subdomain',
        'primary_color',
        'secondary_color',
        'accent_color',
        'logo_url',
        'appointment_slot_minutes',
        'business_hours_start',
        'business_hours_end',
        'business_days',
    ];

    protected $casts = [
        'business_days' => 'array',
    ];

    /**
     * Allowed slot durations in minutes.
     */
    public const SLOT_OPTIONS = [15, 30, 60];

    /**
     * ISO day of week: 1=Mon, 7=Sun.
     */
    public const BUSINESS_DAYS_OPTIONS = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];

    /**
     * Physicians belonging to this organization.
     */
    public function physicians(): BelongsToMany
    {
        return $this->belongsToMany(Physician::class, 'organization_physician');
    }

    /**
     * Staff users (non-physician) belonging to this organization.
     */
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')->withTimestamps();
    }

    /**
     * Holidays when the organization is closed.
     */
    public function holidays(): HasMany
    {
        return $this->hasMany(OrganizationHoliday::class, 'organization_id');
    }

    /**
     * Pending and past invitations for this organization.
     */
    public function invites(): HasMany
    {
        return $this->hasMany(Invite::class, 'organization_id');
    }

    /**
     * Total user count (staff + physicians + patients) for plan usage display.
     */
    public function getUserCount(): int
    {
        $staffCount = $this->staff()->count();
        $physicianCount = $this->physicians()->count();
        $patientCount = \App\Models\Patient::whereHas('physicians', function ($q) {
            $q->whereIn('physicians.id', $this->physicians()->pluck('physicians.id'));
        })->count();

        return $staffCount + $physicianCount + $patientCount;
    }

    /**
     * Plan user limit from subscription, or default.
     */
    public function getPlanLimit(): ?int
    {
        $limits = config('billing.plan_limits', []);
        if (empty($limits)) {
            return config('billing.default_plan_limit', 500);
        }

        $subscription = $this->subscription('default');
        if (! $subscription) {
            return config('billing.default_plan_limit', 500);
        }

        $price = $subscription->stripe_price ?? $subscription->items->first()?->stripe_price;

        return $limits[$price] ?? config('billing.default_plan_limit', 500);
    }

    /**
     * Whether the organization is on the highest plan.
     */
    public function isOnHighestPlan(): bool
    {
        return $this->getPlanLimit() >= config('billing.highest_plan_limit', 10000);
    }
}
