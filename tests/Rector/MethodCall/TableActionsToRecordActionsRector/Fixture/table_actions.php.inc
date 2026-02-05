<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;

function configureTable(Table $table): Table
{
    return $table->actions([
        \Filament\Tables\Actions\EditAction::make(),
    ]);
}

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;

function configureTable(Table $table): Table
{
    return $table->recordActions([
        \Filament\Tables\Actions\EditAction::make(),
    ]);
}

?>
