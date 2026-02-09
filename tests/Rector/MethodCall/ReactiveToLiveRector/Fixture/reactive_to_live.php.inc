<?php

namespace App\Filament\Resources;

use Filament\Schemas\Components\TextInput;

TextInput::make('name')->reactive();

?>
-----
<?php

namespace App\Filament\Resources;

use Filament\Schemas\Components\TextInput;

TextInput::make('name')->live();

?>
