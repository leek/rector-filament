<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;

function configureTable(Table $table): Table
{
    return $table->filters(layout: FiltersLayout::AboveContent, filters: [
        \Filament\Tables\Filters\Filter::make('active'),
    ]);
}

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Tables\Table;
use Filament\Tables\Enums\FiltersLayout;

function configureTable(Table $table): Table
{
    return $table->filters([
        \Filament\Tables\Filters\Filter::make('active'),
    ])->filtersLayout(FiltersLayout::AboveContent);
}

?>
