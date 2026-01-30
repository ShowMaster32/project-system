<?php

namespace App\Filament\User\Resources\Users;

use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Users';

    protected static ?string $slug = 'users';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(255),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255),

            TextInput::make('password')
                ->label('Password')
                ->password()
                ->minLength(8)
                ->required(fn (string $operation) => $operation === 'create')
                ->visible(fn (string $operation) => $operation === 'create')
                ->dehydrateStateUsing(fn ($state) => $state ? bcrypt($state) : null),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        $currentProjectId = session('current_project_id');
        $role = session('current_user_role');
        $isGlobalAdmin = auth()->user()?->isGlobalAdmin();
        $canManage = ($role === 'admin') || $isGlobalAdmin;

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('current_role')
                    ->label('Role')
                    ->getStateUsing(function (User $record) use ($currentProjectId) {
                        if ($record->current_role) {
                            return ucfirst(str_replace('_', ' ', $record->current_role));
                        }
                        if ($currentProjectId) {
                            $role = $record->projects()
                                ->where('project_id', $currentProjectId)
                                ->first()?->pivot?->role;
                            return $role ? ucfirst(str_replace('_', ' ', $role)) : '—';
                        }
                        return '—';
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'Admin' => 'danger',
                        'Coordinator' => 'warning',
                        'Wp leader' => 'info',
                        'Task leader' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->queries(
                        true: fn (Builder $query) => $query->where('users.is_active', true),
                        false: fn (Builder $query) => $query->where('users.is_active', false),
                        blank: fn (Builder $query) => $query,
                    ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Show'),

                Tables\Actions\EditAction::make()
                    ->visible($canManage),

                Tables\Actions\DeleteAction::make()
                    ->visible($canManage),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible($canManage),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $currentProjectId = session('current_project_id');
        $user = auth()->user();

        if ($currentProjectId) {
            $query->leftJoin('project_user as pu', function ($join) use ($currentProjectId) {
                $join->on('pu.user_id', '=', 'users.id')
                    ->where('pu.project_id', '=', $currentProjectId);
            });
            $query->addSelect('users.*');
            $query->addSelect(['current_role' => \DB::raw('pu.role')]);
        }

        $isProjectAdmin = session('current_user_role') === 'admin';
        $isGlobalAdmin = method_exists($user, 'isGlobalAdmin') && $user?->isGlobalAdmin();

        if ($isProjectAdmin && !$isGlobalAdmin && $currentProjectId) {
            $query->whereExists(function ($sub) use ($currentProjectId) {
                $sub->selectRaw('1')
                    ->from('project_user as puf')
                    ->whereColumn('puf.user_id', 'users.id')
                    ->where('puf.project_id', $currentProjectId);
            });
        }

        return $query;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'ArtemisWorkspace';
    }

    public static function canCreate(): bool
    {
        return session('current_user_role') === 'admin' || auth()->user()?->isGlobalAdmin();
    }

    public static function canEdit($record): bool
    {
        return session('current_user_role') === 'admin' || auth()->user()?->isGlobalAdmin();
    }

    public static function canDelete($record): bool
    {
        return session('current_user_role') === 'admin' || auth()->user()?->isGlobalAdmin();
    }
}
