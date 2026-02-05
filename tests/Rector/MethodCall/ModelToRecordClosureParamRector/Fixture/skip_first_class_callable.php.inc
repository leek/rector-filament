<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('view')
    ->visible($this->formatHour(...));

?>
