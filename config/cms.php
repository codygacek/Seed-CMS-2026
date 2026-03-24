<?php

return [
    'disabled_resources' => array_filter(array_map('trim', explode(',', env('CMS_DISABLED_RESOURCES', '')))),

    // Optional: simple presets for “profiles”
    'profile' => env('CMS_PROFILE', 'fraternity'),
];