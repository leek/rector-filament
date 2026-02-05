<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;

function configureTable(Table $table): Table
{
    return $table->bulkActions([
        \Filament\Tables\Actions\BulkActionGroup::make([
            \Filament\Tables\Actions\DeleteBulkAction::make(),
        ]),
    ]);
}

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;

function configureTable(Table $table): Table
{
    return $table->toolbarActions([
        \Filament\Tables\Actions\BulkActionGroup::make([
            \Filament\Tables\Actions\DeleteBulkAction::make(),
        ]),
    ]);
}

?>
