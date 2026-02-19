<?php

use Filament\Forms\Components\Placeholder;

class MyForm
{
    public function getPlaceholder(): Placeholder
    {
        return Placeholder::make('name')->content('value');
    }

    public function getClosure(): void
    {
        $fn = function (): Placeholder {
            return Placeholder::make('name');
        };

        $arrow = fn (): Placeholder => Placeholder::make('name');
    }
}

?>
-----
<?php

use Filament\Infolists\Components\TextEntry;

class MyForm
{
    public function getPlaceholder(): TextEntry
    {
        return TextEntry::make('name')->state('value');
    }

    public function getClosure(): void
    {
        $fn = function (): TextEntry {
            return TextEntry::make('name');
        };

        $arrow = fn (): TextEntry => TextEntry::make('name');
    }
}

?>
