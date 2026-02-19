<?php

use Filament\Forms\Components\Placeholder, App\Models\User;
use Filament\Infolists\Components\TextEntry;

Placeholder::make('created_at')
    ->label('Created')
    ->content(fn ($record) => $record->created_at->format('Y-m-d'));

TextEntry::make('status')
    ->label('Status');

?>
-----
<?php

use App\Models\User;
use Filament\Infolists\Components\TextEntry;

TextEntry::make('created_at')
    ->label('Created')
    ->state(fn ($record) => $record->created_at->format('Y-m-d'));

TextEntry::make('status')
    ->label('Status');

?>
