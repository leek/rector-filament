# Rector Rules for Filament

[Rector](https://github.com/rectorphp/rector) rules for [Filament](https://filamentphp.com/) v4 upgrades and code quality.

## Install

```bash
composer require --dev leek/rector-filament
```

## Usage

### Option A: Set Provider (auto-detect Filament version)

```php
use RectorFilament\Set\FilamentSetProvider;

return RectorConfig::configure()
    ->withSetProviders(FilamentSetProvider::class)
    ->withComposerBased();
```

### Option B: Explicit sets

```php
use RectorFilament\Set\FilamentSetList;

return RectorConfig::configure()
    ->withSets([
        FilamentSetList::FILAMENT_40,
        FilamentSetList::FILAMENT_CODE_QUALITY,
    ]);
```

## Available Sets

| Set | Constant | Description |
|-----|----------|-------------|
| Filament v4 | `FilamentSetList::FILAMENT_40` | Rules for upgrading to Filament v4 |
| Code Quality | `FilamentSetList::FILAMENT_CODE_QUALITY` | Convention and bug-prevention rules |

## Rules

### Filament v4 (`FILAMENT_40`)

#### ActionFormToSchemaRector

Renames `->form()` to `->schema()` on Filament Action classes (v4 API change).

```diff
 Action::make('export')
-    ->form([
+    ->schema([
         TextInput::make('name'),
     ])
     ->action(fn () => null);
```

#### TableActionsNamespaceRector

Updates `Filament\Tables\Actions\*` imports to `Filament\Actions\*` (v4 namespace move).

```diff
-use Filament\Tables\Actions\EditAction;
-use Filament\Tables\Actions\DeleteAction;
+use Filament\Actions\EditAction;
+use Filament\Actions\DeleteAction;
```

### Code Quality (`FILAMENT_CODE_QUALITY`)

#### NullableAfterStateUpdatedStateRector

Makes `$state` parameter nullable in `afterStateUpdated` callbacks to prevent type errors when fields are cleared.

```diff
 TextInput::make('name')
-    ->afterStateUpdated(function (string $state) {
+    ->afterStateUpdated(function (?string $state) {
         // ...
     });
```

#### ModifyQueryUsingBuilderToQueryRector

Renames `$builder` to `$query` in `modifyQueryUsing` callbacks to follow Filament convention.

```diff
-->modifyQueryUsing(fn (Builder $builder) => $builder->where('active', true))
+->modifyQueryUsing(fn (Builder $query) => $query->where('active', true))
```

#### FilamentUtilityInjectionTypeRector

Adds proper type hints for Filament utility injection parameters (`$get`, `$set`, `$record`, `$operation`, `$component`, `$livewire`) in closures.

```diff
 TextInput::make('name')
-    ->visible(function ($get, $record) {
+    ->visible(function (Get $get, ?Model $record) {
         return $get('type') === 'business';
     });
```

## License

MIT
