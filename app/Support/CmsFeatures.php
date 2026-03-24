<?php

namespace App\Support;

class CmsFeatures
{
    public static function resourceEnabled(string $key): bool
    {
        $disabled = config('cms.disabled_resources', []);

        // Normalize keys so you can use either "members" or "MemberResource" etc.
        $key = strtolower(trim($key));

        return ! in_array($key, array_map('strtolower', $disabled), true);
    }
}