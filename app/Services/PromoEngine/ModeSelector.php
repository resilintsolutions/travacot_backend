<?php

namespace App\Services\PromoEngine;

class ModeSelector
{
    public function orderedModes(array $enabledModes): array
    {
        $priority = ['aggressive', 'normal', 'light'];

        return array_values(array_filter($priority, function ($mode) use ($enabledModes) {
            return in_array($mode, $enabledModes, true);
        }));
    }
}
