<?php

namespace App\Filament\User\Resources\WorkPackages;

use App\Filament\User\Resources\WorkPackages\Pages\ManageWorkPackages;
use App\Models\WorkPackage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkPackageResource extends Resource
{
    protected static ?string $model = WorkPackage::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Work Package';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'Progetto';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('code')
                    ->label('Codice')
                    ->maxLength(50),
                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Descrizione')
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->label('Data Inizio')
                    ->required(),
                DatePicker::make('end_date')
                    ->label('Data Fine')
                    ->required(),
                TextInput::make('duration_days')
                    ->label('Durata (giorni)')
                    ->numeric(),
                Select::make('status')
                    ->label('Stato')
                    ->options([
                        'active'    => 'Attivo',
                        'completed' => 'Completato',
                        'on_hold'   => 'In Pausa',
                        'cancelled' => 'Annullato',
                    ])
                    ->required()
                    ->default('active'),
                TextInput::make('progress')
                    ->label('Avanzamento (%)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                TextInput::make('color')
                    ->label('Colore')
                    ->required()
                    ->default('#3b82f6')
                    ->maxLength(20),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('code')
                    ->label('Codice')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_date')
                    ->label('Inizio')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fine')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('duration_days')
                    ->label('Giorni')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active'    => 'success',
                        'completed' => 'info',
                        'on_hold'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->searchable(),
                TextColumn::make('progress')
                    ->label('%')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
            ])
            ->recordActions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('work_packages.edit')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('work_packages.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageWorkPackages::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        $projectId = session('current_project_id');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        return $query;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasProjectPermission('work_packages.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasProjectPermission('work_packages.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasProjectPermission('work_packages.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasProjectPermission('work_packages.view') ?? false;
    }
}
