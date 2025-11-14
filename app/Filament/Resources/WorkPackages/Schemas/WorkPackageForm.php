<?php

namespace App\Filament\Resources\WorkPackages\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class WorkPackageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('project_id')
                    ->required()
                    ->numeric(),
                TextInput::make('code'),
                TextInput::make('name')
                    ->required(),
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
                    ->default('active'),
                TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('color')
                    ->required()
                    ->default('#3b82f6'),
            ]);
    }
}
