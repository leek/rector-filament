<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('view')
    ->visible(function ($model) {
        $inner = array_map(function ($model) {
            return $model->name;
        }, $model->items);

        return $model->is_active;
    });

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('view')
    ->visible(function ($record) {
        $inner = array_map(function ($model) {
            return $model->name;
        }, $record->items);

        return $record->is_active;
    });

?>
