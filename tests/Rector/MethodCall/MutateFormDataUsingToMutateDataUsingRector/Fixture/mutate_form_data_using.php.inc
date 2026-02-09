<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('create')
    ->mutateFormDataUsing(fn (array $data) => $data)
    ->action(fn () => null);

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('create')
    ->mutateDataUsing(fn (array $data) => $data)
    ->action(fn () => null);

?>
