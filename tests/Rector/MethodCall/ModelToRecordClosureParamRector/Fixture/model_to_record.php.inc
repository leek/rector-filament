<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('view')
    ->visible(function ($model) {
        return $model->is_active;
    });

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Actions\Action;

Action::make('view')
    ->visible(function ($record) {
        return $record->is_active;
    });

?>
