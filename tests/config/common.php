<?php

return [
    'params' => [
        // The module lives next to the HumHub checkout in CI and may also be
        // located outside protected/modules in local development.
        'moduleAutoloadPaths' => [dirname(__DIR__, 3)],
    ],
];
