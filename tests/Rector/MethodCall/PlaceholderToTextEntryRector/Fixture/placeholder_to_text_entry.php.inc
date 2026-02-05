<?php

use Filament\Forms\Components\Placeholder;

Placeholder::make('created_at')
    ->label('Created')
    ->content(fn ($record) => $record->created_at->format('Y-m-d'));

?>
-----
<?php

use Filament\Infolists\Components\TextEntry;

TextEntry::make('created_at')
    ->label('Created')
    ->state(fn ($record) => $record->created_at->format('Y-m-d'));

?>
