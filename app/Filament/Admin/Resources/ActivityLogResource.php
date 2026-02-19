<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ActivityLogResource\Pages;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Activity Log';
    protected static ?string $slug            = 'activity-log';
    protected static ?string $modelLabel      = 'Log';
    protected static ?string $pluralModelLabel = 'Activity Log';
    protected static ?int    $navigationSort  = 10;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->searchable(false),

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
                    ->label('Eseguito da')
                    ->searchable()
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

                Tables\Columns\TextColumn::make('properties')
                    ->label('Modifiche')
                    ->formatStateUsing(function ($state) {
                        if (! $state || ! isset($state['attributes'])) {
                            return '—';
                        }
                        $changes = [];
                        $old = $state['old'] ?? [];
                        $new = $state['attributes'] ?? [];
                        foreach ($new as $key => $val) {
                            if (isset($old[$key]) && $old[$key] !== $val) {
                                $changes[] = "{$key}: {$old[$key]} → {$val}";
                            }
                        }
                        return implode("\n", $changes) ?: '—';
                    })
                    ->wrap()
                    ->limit(120),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Tipo oggetto')
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

                Filter::make('this_week')
                    ->label('Questa settimana')
                    ->query(fn (Builder $query) => $query->whereBetween('created_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek(),
                    ])),
            ])
            ->actions([])
            ->bulkActions([])
            ->paginated([25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLog::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
