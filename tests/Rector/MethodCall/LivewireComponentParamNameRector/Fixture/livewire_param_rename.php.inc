<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;
use Livewire\Component;

Action::make('test')
    ->visible(function (\Livewire\Component $component) {
        return $component->getData();
    });

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;
use Livewire\Component;

Action::make('test')
    ->visible(function (\Livewire\Component $livewire) {
        return $livewire->getData();
    });

?>
