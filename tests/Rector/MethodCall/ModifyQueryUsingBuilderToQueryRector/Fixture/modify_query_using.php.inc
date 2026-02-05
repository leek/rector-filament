<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;

Select::make('status')
    ->modifyQueryUsing(fn (Builder $builder) => $builder->where('active', true));

?>
-----
<?php

namespace App\Filament\Resources;

use Illuminate\Database\Eloquent\Builder;

Select::make('status')
    ->modifyQueryUsing(fn (Builder $query) => $query->where('active', true));

?>
