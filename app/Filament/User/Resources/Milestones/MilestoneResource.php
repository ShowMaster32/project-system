<?php

namespace App\Filament\User\Resources\Milestones;

use App\Filament\User\Resources\Milestones\Pages\ManageMilestones;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkPackage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MilestoneResource extends Resource
{
    protected static ?string $model = Milestone::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationLabel = 'Milestone';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $projectId = session('current_project_id');

        $projectUsers = User::whereHas('projects', fn ($q) => $q->where('projects.id', $projectId))
            ->orderBy('name')
            ->pluck('name', 'id');

        return $schema->components([
            Select::make('work_package_id')
                ->label('Work Package')
                ->options(
                    WorkPackage::where('project_id', $projectId)->orderBy('name')->pluck('name', 'id')
                )
                ->searchable()
                ->nullable()
                ->live()
                ->afterStateUpdated(fn ($set) => $set('task_id', null)),

            Select::make('task_id')
                ->label('Task associato')
                ->options(function ($get) use ($projectId) {
                    $wpId = $get('work_package_id');
                    return Task::where('project_id', $projectId)
                        ->when($wpId, fn ($q) => $q->where('work_package_id', $wpId))
                        ->orderBy('name')
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->nullable(),

            TextInput::make('code')
                ->label('Codice')
                ->maxLength(50)
                ->placeholder('M01'),

            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Descrizione')
                ->rows(3)
                ->columnSpanFull(),

            Select::make('leader_id')
                ->label('Responsabile')
                ->options($projectUsers)
                ->searchable()
                ->nullable(),

            DatePicker::make('due_date')
                ->label('Data scadenza')
                ->required(),

            Select::make('status')
                ->label('Stato')
                ->options([
                    'pending'     => 'In attesa',
                    'in_progress' => 'In corso',
                    'completed'   => 'Completato',
                    'cancelled'   => 'Annullato',
                ])
                ->required()
                ->default('pending'),

            DateTimePicker::make('completed_at')
                ->label('Completato il')
                ->nullable()
                ->visible(fn ($get) => $get('status') === 'completed'),
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
                    ->sortable()
                    ->default('—'),

                TextColumn::make('leader.name')
                    ->label('Responsabile')
                    ->default('—'),

                TextColumn::make('due_date')
                    ->label('Scadenza')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Milestone $record) => $record->completed_at
                        ? null
                        : (now()->gt($record->due_date) ? 'danger' : null)
                    ),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed'   => 'success',
                        'in_progress' => 'info',
                        'cancelled'   => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'     => 'In attesa',
                        'in_progress' => 'In corso',
                        'completed'   => 'Completato',
                        'cancelled'   => 'Annullato',
                        default       => $state,
                    }),

                TextColumn::make('completed_at')
                    ->label('Completato il')
                    ->dateTime('d/m/Y H:i')
                    ->default('—')
                    ->sortable(),
            ])
            ->defaultSort('due_date')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'     => 'In attesa',
                        'in_progress' => 'In corso',
                        'completed'   => 'Completato',
                        'cancelled'   => 'Annullato',
                    ]),

                SelectFilter::make('work_package_id')
                    ->label('Work Package')
                    ->options(fn () => WorkPackage::where('project_id', session('current_project_id'))
                        ->pluck('name', 'id')),
            ])
            ->recordActions([
                // Azione rapida "Segna completato"
                Action::make('complete')
                    ->label('Completa')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Completare la milestone?')
                    ->action(function (Milestone $record) {
                        $record->update([
                            'status'       => 'completed',
                            'completed_at' => now(),
                        ]);
                    })
                    ->visible(fn (Milestone $record) =>
                        $record->status !== 'completed'
                        && auth()->user()?->hasProjectPermission('milestones.complete')
                    ),

                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('milestones.edit')),

                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('milestones.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMilestones::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $projectId = session('current_project_id');
        return parent::getEloquentQuery()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->with(['workPackage:id,name', 'leader:id,name']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasProjectPermission('milestones.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasProjectPermission('milestones.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasProjectPermission('milestones.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasProjectPermission('milestones.view') ?? false;
    }
}
