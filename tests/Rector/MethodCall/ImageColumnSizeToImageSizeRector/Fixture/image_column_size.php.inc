<?php

namespace App\Filament\Resources;

use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')->size(50);

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Tables\Columns\ImageColumn;

ImageColumn::make('avatar')->imageSize(50);

?>
