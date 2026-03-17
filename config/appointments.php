<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default appointment slot duration (minutes)
    |--------------------------------------------------------------------------
    | Used when organization has no custom setting. Allowed: 15, 30, 60.
    */
    'slot_minutes' => 30,

    /*
    |--------------------------------------------------------------------------
    | Default business hours (for slot calculation)
    |--------------------------------------------------------------------------
    | Start and end of the working day. Format: HH:MM (24-hour).
    | Default: 8 AM - 5 PM Monday through Friday.
    */
    'business_hours_start' => '08:00',
    'business_hours_end' => '17:00',

    /*
    |--------------------------------------------------------------------------
    | Default business days (ISO: 1=Mon, 7=Sun)
    |--------------------------------------------------------------------------
    */
    'business_days' => [1, 2, 3, 4, 5],
];
