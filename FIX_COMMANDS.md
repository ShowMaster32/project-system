# Comandi da eseguire su Windows (Herd)

Apri il terminale nella cartella del progetto (`C:\Users\showm\Herd\project-system`) ed esegui in ordine:

## 1. Pulisci cache e configurazione

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
```

## 2. Esegui le migrazioni (FONDAMENTALE - crea le tabelle Spatie permissions)

```bash
php artisan migrate
```

Se ci sono problemi:
```bash
php artisan migrate --force
```

## 3. Rigenera autoload Composer

```bash
composer dump-autoload
```

## 4. (Opzionale) Esegui i seeder per dati di test

```bash
php artisan db:seed
```

## Errori risolti nel codice

- `Filament\Tables\Actions\EditAction/ViewAction/DeleteAction` non esiste in Filament 4.x
  → corretti in `Filament\Actions\EditAction/ViewAction/DeleteAction`
- Metodi `->actions([])` e `->bulkActions([])` deprecati
  → corretti in `->recordActions([])` e `->toolbarActions([])`
- Namespace errati in ProjectResource, TaskResource e relative Pages
  → corretti da `App\Filament\Resources\*` a `App\Filament\User\Resources\*`
- `Filament\Infolists\Components\Grid/Section` non esistono in Filament 4.x
  → corretti in `Filament\Schemas\Components\Grid/Section`
- Namespace errati in ListWorkPackages.php e ViewWorkPackage.php
  → corretti da `WorkPackageResource\Pages` a `WorkPackages\Pages`
- Tabella `permissions` mancante → eseguire `php artisan migrate`
