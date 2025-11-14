<?php

namespace App\Filament\Resources\WorkPackages;

use App\Filament\Resources\WorkPackages\Pages\CreateWorkPackage;
use App\Filament\Resources\WorkPackages\Pages\EditWorkPackage;
use App\Filament\Resources\WorkPackages\Pages\ListWorkPackages;
use App\Filament\Resources\WorkPackages\Schemas\WorkPackageForm;
use App\Filament\Resources\WorkPackages\Tables\WorkPackagesTable;
use App\Models\WorkPackage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkPackageResource extends Resource
{
    protected static ?string $model = WorkPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'project-system';

    public static function form(Schema $schema): Schema
    {
        return WorkPackageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkPackagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkPackages::route('/'),
            'create' => CreateWorkPackage::route('/create'),
            'edit' => EditWorkPackage::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
