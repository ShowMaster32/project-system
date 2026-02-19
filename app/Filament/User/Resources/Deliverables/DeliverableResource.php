<?php

namespace App\Filament\User\Resources\Deliverables;

use App\Filament\User\Resources\Deliverables\Pages\ManageDeliverables;
use App\Models\Deliverable;
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
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DeliverableResource extends Resource
{
    protected static ?string $model = Deliverable::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationLabel = 'Deliverable';
    protected static ?int    $navigationSort  = 4;
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

            Select::make('milestone_id')
                ->label('Milestone')
                ->options(
                    Milestone::where('project_id', $projectId)->orderBy('name')->pluck('name', 'id')
                )
                ->searchable()
                ->nullable(),

            TextInput::make('code')
                ->label('Codice')
                ->maxLength(50)
                ->placeholder('D01'),

            TextInput::make('name')
                ->label('Nome')
                ->required()
                ->maxLength(255)
                ->columnSpanFull(),

            Textarea::make('description')
                ->label('Descrizione')
                ->rows(3)
                ->columnSpanFull(),

            Select::make('responsible_id')
                ->label('Responsabile')
                ->options($projectUsers)
                ->searchable()
                ->nullable(),

            DatePicker::make('due_date')
                ->label('Data consegna prevista')
                ->required(),

            Select::make('status')
                ->label('Stato')
                ->options([
                    'pending'    => 'In attesa',
                    'in_progress' => 'In lavorazione',
                    'delivered'  => 'Consegnato',
                    'validated'  => 'Validato',
                    'rejected'   => 'Rifiutato',
                ])
                ->required()
                ->default('pending'),

            Toggle::make('requires_validation')
                ->label('Richiede validazione')
                ->default(true)
                ->columnSpanFull(),

            DateTimePicker::make('delivered_at')
                ->label('Consegnato il')
                ->nullable(),

            DateTimePicker::make('validated_at')
                ->label('Validato il')
                ->nullable()
                ->visible(fn ($get) => in_array($get('status'), ['validated', 'rejected'])),

            Select::make('validated_by')
                ->label('Validato da')
                ->options($projectUsers)
                ->nullable()
                ->visible(fn ($get) => in_array($get('status'), ['validated', 'rejected'])),

            Textarea::make('validation_notes')
                ->label('Note di validazione')
                ->rows(2)
                ->columnSpanFull()
                ->visible(fn ($get) => in_array($get('status'), ['validated', 'rejected'])),
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
                    ->default('—'),

                TextColumn::make('responsible.name')
                    ->label('Responsabile')
                    ->default('—'),

                TextColumn::make('due_date')
                    ->label('Consegna')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn (Deliverable $record) =>
                        ! in_array($record->status, ['delivered', 'validated'])
                        && now()->gt($record->due_date) ? 'danger' : null
                    ),

                TextColumn::make('status')
                    ->label('Stato')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'validated'   => 'success',
                        'delivered'   => 'info',
                        'in_progress' => 'warning',
                        'rejected'    => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending'     => 'In attesa',
                        'in_progress' => 'In lavorazione',
                        'delivered'   => 'Consegnato',
                        'validated'   => 'Validato',
                        'rejected'    => 'Rifiutato',
                        default       => $state,
                    }),

                IconColumn::make('requires_validation')
                    ->label('Val.')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-minus-circle')
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->defaultSort('due_date')
            ->filters([
                SelectFilter::make('status')
                    ->label('Stato')
                    ->options([
                        'pending'     => 'In attesa',
                        'in_progress' => 'In lavorazione',
                        'delivered'   => 'Consegnato',
                        'validated'   => 'Validato',
                        'rejected'    => 'Rifiutato',
                    ]),

                TernaryFilter::make('requires_validation')
                    ->label('Richiede validazione'),

                SelectFilter::make('work_package_id')
                    ->label('Work Package')
                    ->options(fn () => WorkPackage::where('project_id', session('current_project_id'))
                        ->pluck('name', 'id')),
            ])
            ->recordActions([
                // Azione "Segna consegnato"
                Action::make('deliver')
                    ->label('Consegna')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Segnare come consegnato?')
                    ->action(fn (Deliverable $record) => $record->update([
                        'status'       => 'delivered',
                        'delivered_at' => now(),
                    ]))
                    ->visible(fn (Deliverable $record) =>
                        $record->status === 'in_progress'
                        && auth()->user()?->hasProjectPermission('deliverables.edit')
                    ),

                // Azione "Valida"
                Action::make('validate')
                    ->label('Valida')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Validare il deliverable?')
                    ->form([
                        Textarea::make('validation_notes')
                            ->label('Note di validazione (opzionale)')
                            ->rows(2),
                    ])
                    ->action(function (Deliverable $record, array $data) {
                        $record->update([
                            'status'           => 'validated',
                            'validated_at'     => now(),
                            'validated_by'     => auth()->id(),
                            'validation_notes' => $data['validation_notes'] ?? null,
                        ]);
                    })
                    ->visible(fn (Deliverable $record) =>
                        $record->status === 'delivered'
                        && $record->requires_validation
                        && auth()->user()?->hasProjectPermission('deliverables.validate')
                    ),

                // Azione "Rifiuta"
                Action::make('reject')
                    ->label('Rifiuta')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Rifiutare il deliverable?')
                    ->form([
                        Textarea::make('validation_notes')
                            ->label('Motivazione rifiuto')
                            ->required()
                            ->rows(2),
                    ])
                    ->action(function (Deliverable $record, array $data) {
                        $record->update([
                            'status'           => 'rejected',
                            'validated_at'     => now(),
                            'validated_by'     => auth()->id(),
                            'validation_notes' => $data['validation_notes'],
                        ]);
                    })
                    ->visible(fn (Deliverable $record) =>
                        $record->status === 'delivered'
                        && $record->requires_validation
                        && auth()->user()?->hasProjectPermission('deliverables.validate')
                    ),

                EditAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('deliverables.edit')),

                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('deliverables.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDeliverables::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $projectId = session('current_project_id');
        return parent::getEloquentQuery()
            ->when($projectId, fn ($q) => $q->where('project_id', $projectId))
            ->with(['workPackage:id,name', 'responsible:id,name']);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasProjectPermission('deliverables.create') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasProjectPermission('deliverables.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasProjectPermission('deliverables.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasProjectPermission('deliverables.view') ?? false;
    }
}
