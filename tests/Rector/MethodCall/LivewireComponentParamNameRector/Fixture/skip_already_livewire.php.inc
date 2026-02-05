<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('test')
    ->visible(function (\Livewire\Component $livewire) {
        return $livewire->getData();
    });

?>
