<?php

namespace App\Filament\Resources;

use Filament\Schemas\Components\TextInput;

TextInput::make('name')->label('');

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Schemas\Components\TextInput;

TextInput::make('name')->hiddenLabel();

?>
