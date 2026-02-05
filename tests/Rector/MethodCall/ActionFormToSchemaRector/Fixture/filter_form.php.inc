<?php

namespace App\Filament\Resources;

use Filament\Tables\Filters\Filter;

Filter::make('active')
    ->form([
        \Filament\Forms\Components\Toggle::make('is_active'),
    ]);

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Tables\Filters\Filter;

Filter::make('active')
    ->schema([
        \Filament\Forms\Components\Toggle::make('is_active'),
    ]);

?>
