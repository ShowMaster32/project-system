<?php

namespace App\Filament\User\Resources;

use App\Filament\User\Resources\RoleResource\Pages;
use App\Models\Role;
use App\Models\Permission;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Ruoli';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'roles';

    // METODO invece di proprietà per Filament 4.x
    public static function getNavigationGroup(): string|null
    {
        return 'Amministrazione';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Informazioni Ruolo')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome Ruolo')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('Descrizione')
                        ->maxLength(500)
                        ->columnSpanFull(),

                    TextInput::make('level')
                        ->label('Livello Gerarchia')
                        ->numeric()
                        ->default(10),

                    Toggle::make('is_default')
                        ->label('Ruolo Default'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->label('Descrizione')
                    ->limit(50),

                Tables\Columns\TextColumn::make('level')
                    ->label('Livello')
                    ->sortable()
                    ->badge(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
