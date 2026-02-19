<?php

namespace App\Filament\User\Resources\Tasks;

use App\Filament\User\Resources\Tasks\Pages\ManageTasks;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkPackage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Task';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $projectId = session('current_project_id');

        $projectUsers = User::whereHas('projects', fn ($q) => $q->where('projects.id', $projectId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return $schema
            ->components([
                Select::make('work_package_id')
                    ->label('Work Package')
                    ->options(
                        WorkPackage::when($projectId, fn ($q) => $q->where('project_id', $projectId))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->searchable(),

                TextInput::make('code')
                    ->label('Codice')
                    ->maxLength(50)
                    ->placeholder('T01'),

                TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->label('Descrizione')
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('leader_id')
                    ->label('Task Leader')
                    ->options($projectUsers)
                    ->searchable()
                    ->nullable(),

                Select::make('assigned_to')
                    ->label('Assegnato a')
                    ->options($projectUsers)
                    ->searchable()
                    ->nullable(),

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
                        'pending'     => 'In attesa',
                        'in_progress' => 'In corso',
                        'completed'   => 'Completato',
                        'on_hold'     => 'In pausa',
                        'cancelled'   => 'Annullato',
                    ])
                    ->required()
                    ->default('pending'),

                TextInput::make('progress')
                    ->label('Avanzamento (%)')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),

                ColorPicker::make('color')
                    ->label('Colore Gantt')
                    ->nullable(),

                Toggle::make('is_critical_path')
                    ->label('Percorso critico')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Codice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('workPackage.name')
                    ->label('Work Package')
                    ->sortable(),
                TextColumn::make('assignedUser.name')
                    ->label('Assegnato a')
                    ->default('—'),
                TextColumn::make('end_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Task $record) =>
                        ! in_array($record->status, ['completed', 'cancelled'])
                        && now()->gt($record->end_date) ? 'danger' : null
                    ),
                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'completed'   => 'success',
                        'on_hold'     => 'warning',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'     => 'In attesa',
                        'in_progress' => 'In corso',
                        'completed'   => 'Completato',
                        'on_hold'     => 'In pausa',
                        'cancelled'   => 'Annullato',
                        default       => $state,
                    }),
                TextColumn::make('progress')
                    ->label('%')
                    ->numeric()
                    ->sortable()
                    ->suffix('%'),
                IconColumn::make('is_critical_path')
                    ->label('Critico')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'     => 'In attesa',
                        'in_progress' => 'In corso',
                        'completed'   => 'Completato',
                        'on_hold'     => 'In pausa',
                        'cancelled'   => 'Annullato',
                    ]),
                SelectFilter::make('work_package_id')
                    ->label('Work Package')
                    ->options(fn () => WorkPackage::where('project_id', session('current_project_id'))
                        ->pluck('name', 'id')),
                SelectFilter::make('assigned_to')
                    ->label('Assegnato a')
                    ->options(fn () => User::whereHas('projects', fn ($q) => $q->where('projects.id', session('current_project_id')))
                        ->pluck('name', 'id')),
            ])
            ->recordActions([
                // Cambio stato rapido
                Action::make('change_status')
                    ->label('Cambia stato')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->form([
                        Select::make('status')
                            ->label('Nuovo stato')
                            ->options([
                                'pending'     => 'In attesa',
                                'in_progress' => 'In corso',
                                'completed'   => 'Completato',
                                'on_hold'     => 'In pausa',
                                'cancelled'   => 'Annullato',
                            ])
                            ->required(),
                        TextInput::make('progress')
                            ->label('Avanzamento (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->fillForm(fn (Task $record) => [
                        'status'   => $record->status,
                        'progress' => $record->progress,
                    ])
                    ->action(fn (Task $record, array $data) => $record->update($data))
                    ->visible(fn () => auth()->user()?->hasProjectPermission('tasks.change_status')),

                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('tasks.edit')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('tasks.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasProjectPermission('tasks.delete')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTasks::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        $projectId = session('current_project_id');
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        // Eager load workPackage per evitare N+1 nella colonna workPackage.name
        return $query->with('workPackage:id,name,code');
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasProjectPermission('tasks.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasProjectPermission('tasks.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasProjectPermission('tasks.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasProjectPermission('tasks.view') ?? false;
    }
}
