<?php

return [
    // Hours allowed to resolve a ticket, by priority
    'hours' => [
        'critical' => 2,
        'high' => 8,
        'medium' => 24,
        'low' => 72,
    ],

    // Warn the agent once this fraction of the time has passed
    'warn_at' => 0.8,
];