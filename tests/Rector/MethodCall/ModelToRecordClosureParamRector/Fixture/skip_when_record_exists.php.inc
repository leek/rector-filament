<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('view')
    ->visible(function ($model, $record) {
        return $record->is_active;
    });

?>
