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

#### Namespace Changes

##### ActionsNamespaceRector

Updates action imports from `Tables`, `Notifications`, and `Forms` namespaces to `Filament\Actions\*`.

```diff
-use Filament\Tables\Actions\EditAction;
-use Filament\Notifications\Actions\Action;
-use Filament\Forms\Actions\Action;
+use Filament\Actions\EditAction;
+use Filament\Actions\Action;
+use Filament\Actions\Action;
```

##### GetSetNamespaceRector

Updates `Get` and `Set` utility imports from `Filament\Forms\*` to `Filament\Schemas\Components\Utilities\*`.

```diff
-use Filament\Forms\Get;
-use Filament\Forms\Set;
+use Filament\Schemas\Components\Utilities\Get;
+use Filament\Schemas\Components\Utilities\Set;
```

##### SchemaComponentsNamespaceRector

Updates layout component imports from `Filament\Forms\Components\*` to `Filament\Schemas\Components\*`.

```diff
-use Filament\Forms\Components\Fieldset;
-use Filament\Forms\Components\Grid;
-use Filament\Forms\Components\Section;
-use Filament\Forms\Components\Split;
-use Filament\Forms\Components\Tabs;
-use Filament\Forms\Components\Wizard;
+use Filament\Schemas\Components\Fieldset;
+use Filament\Schemas\Components\Grid;
+use Filament\Schemas\Components\Section;
+use Filament\Schemas\Components\Split;
+use Filament\Schemas\Components\Tabs;
+use Filament\Schemas\Components\Wizard;
```

#### Method Renames

##### ActionFormToSchemaRector

Renames `->form()` to `->schema()` on Action, BulkAction, and Filter classes.

```diff
 Action::make('export')
-    ->form([
+    ->schema([
         TextInput::make('name'),
     ])
     ->action(fn () => null);
```

##### TableActionsToRecordActionsRector

Renames `->actions()` to `->recordActions()` on Table.

```diff
 $table
-    ->actions([
+    ->recordActions([
         EditAction::make(),
     ]);
```

##### BulkActionsToToolbarActionsRector

Transforms `->bulkActions([...])` to `->toolbarActions([BulkActionGroup::make([...])])` on Table.

```diff
 $table
-    ->bulkActions([
-        DeleteBulkAction::make(),
-    ]);
+    ->toolbarActions([
+        BulkActionGroup::make([
+            DeleteBulkAction::make(),
+        ]),
+    ]);
```

##### FiltersLayoutArgToMethodRector

Extracts the second argument of `->filters()` into a chained `->filtersLayout()` call.

```diff
 $table
-    ->filters([
-        Filter::make('active'),
-    ], layout: FiltersLayout::AboveContent);
+    ->filters([
+        Filter::make('active'),
+    ])
+    ->filtersLayout(FiltersLayout::AboveContent);
```

#### Closure Parameter Renames

##### ModelToRecordClosureParamRector

Renames `$model` to `$record` in Filament closure parameters.

```diff
 Action::make('view')
-    ->visible(function ($model) {
-        return $model->is_active;
+    ->visible(function ($record) {
+        return $record->is_active;
     });
```

##### LivewireComponentParamNameRector

Renames `Livewire\Component` type-hinted parameters to `$livewire` in Filament closures.

```diff
 TextInput::make('name')
-    ->visible(function (Component $component) {
-        return $component->getData();
+    ->visible(function (Component $livewire) {
+        return $livewire->getData();
     });
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
