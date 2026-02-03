<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default daily hours expected per worker
    |--------------------------------------------------------------------------
    |
    | Used when generating shifts or calculating compliance metrics. This value
    | can be overridden per user using the WorkPreference model.
    |
    */
    'default_expected_daily_hours' => 8,

    /*
    |--------------------------------------------------------------------------
    | Event types supported by the module
    |--------------------------------------------------------------------------
    */
    'event_types' => [
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'absence',
    ],
];
