<?php

namespace App\Filament\User\Resources\WorkPackages;

use App\Filament\User\Resources\WorkPackages\Pages\ManageWorkPackages;
use App\Models\WorkPackage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
// use Filament\Support\Icons\Heroicon; // Switched to string icon to avoid version mismatches
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkPackageResource extends Resource
{
    protected static ?string $model = WorkPackage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('code')
                    ->maxLength(50),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('leader_id')
                    ->numeric(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('duration_days')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('active')
                    ->maxLength(50),
                TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('color')
                    ->required()
                    ->default('#3b82f6')
                    ->maxLength(20),
            ]);
    }

    public static function table(Table $table): Table
    {
        $role = session('current_user_role');
        $canEdit = in_array($role, ['admin', 'coordinator']);
        $canDelete = $role === 'admin';

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')->searchable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('leader_id')->numeric()->sortable(),
                TextColumn::make('start_date')->date()->sortable(),
                TextColumn::make('end_date')->date()->sortable(),
                TextColumn::make('duration_days')->numeric()->sortable(),
                TextColumn::make('status')->searchable(),
                TextColumn::make('progress')->numeric()->sortable(),
            ])
            ->recordActions([
                EditAction::make()->visible($canEdit),
                DeleteAction::make()->visible($canDelete),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWorkPackages::route('/'),
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
