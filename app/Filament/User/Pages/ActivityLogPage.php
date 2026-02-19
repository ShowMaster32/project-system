<?php

namespace App\Filament\User\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogPage extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'filament.user.pages.activity-log';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Activity Log';
    protected static ?string $slug            = 'activity-log';
    protected static ?int    $navigationSort  = 10;

    /**
     * Visibile solo ai project_admin.
     */
    public static function canAccess(): bool
    {
        $projectId = session('current_project_id');
        if (! $projectId) {
            return false;
        }
        return auth()->user()?->hasProjectPermission('users.manage', $projectId) ?? false;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Amministrazione';
    }

    public function table(Table $table): Table
    {
        $projectId = session('current_project_id');

        return $table
            ->query(
                Activity::query()
                    ->where(function (Builder $q) use ($projectId) {
                        // Log di Task del progetto
                        $q->where(function (Builder $inner) use ($projectId) {
                            $inner->where('subject_type', \App\Models\Task::class)
                                  ->whereIn('subject_id', \App\Models\Task::where('project_id', $projectId)->pluck('id'));
                        })
                        // Log di WorkPackage del progetto
                        ->orWhere(function (Builder $inner) use ($projectId) {
                            $inner->where('subject_type', \App\Models\WorkPackage::class)
                                  ->whereIn('subject_id', \App\Models\WorkPackage::where('project_id', $projectId)->pluck('id'));
                        })
                        // Log del progetto stesso
                        ->orWhere(function (Builder $inner) use ($projectId) {
                            $inner->where('subject_type', \App\Models\Project::class)
                                  ->where('subject_id', $projectId);
                        });
                    })
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('log_name')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'task'         => 'info',
                        'work_package' => 'warning',
                        'project'      => 'success',
                        default        => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'task'         => 'Task',
                        'work_package' => 'Work Package',
                        'project'      => 'Progetto',
                        default        => ucfirst($state ?? '—'),
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Evento')
                    ->searchable()
                    ->wrap()
                    ->limit(80),

                Tables\Columns\TextColumn::make('causer.name')
                    ->label('Utente')
                    ->default('—'),

                Tables\Columns\TextColumn::make('event')
                    ->label('Azione')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'created' => 'Creato',
                        'updated' => 'Aggiornato',
                        'deleted' => 'Eliminato',
                        default   => ucfirst($state ?? '—'),
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Tipo')
                    ->options([
                        'task'         => 'Task',
                        'work_package' => 'Work Package',
                        'project'      => 'Progetto',
                    ]),

                SelectFilter::make('event')
                    ->label('Azione')
                    ->options([
                        'created' => 'Creato',
                        'updated' => 'Aggiornato',
                        'deleted' => 'Eliminato',
                    ]),

                Filter::make('today')
                    ->label('Solo oggi')
                    ->query(fn (Builder $query) => $query->whereDate('created_at', Carbon::today())),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated([20, 50]);
    }
}
