<?php

namespace App\Services;

class HeatMapService
{
    public function getData(): array
    {
        return array_map($this->formatHour(...), range(6, 21));
    }

    private function formatHour(int $hour): string
    {
        return $hour . 'am';
    }
}

?>
