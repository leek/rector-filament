<?php

namespace App\Filament\Resources;

use Filament\Actions\BulkAction;

BulkAction::make('export')
    ->form([
        \Filament\Forms\Components\TextInput::make('name'),
    ])
    ->action(fn () => null);

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Actions\BulkAction;

BulkAction::make('export')
    ->schema([
        \Filament\Forms\Components\TextInput::make('name'),
    ])
    ->action(fn () => null);

?>
