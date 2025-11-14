<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('project_id')
                    ->required()
                    ->numeric(),
                TextInput::make('work_package_id')
                    ->required()
                    ->numeric(),
                TextInput::make('parent_id')
                    ->numeric(),
                TextInput::make('code'),
                TextInput::make('name')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                TextInput::make('leader_id')
                    ->numeric(),
                TextInput::make('assigned_to')
                    ->numeric(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date')
                    ->required(),
                TextInput::make('duration_days')
                    ->numeric(),
                TextInput::make('depends_on')
                    ->required()
                    ->default('[]'),
                TextInput::make('status')
                    ->required()
                    ->default('pending'),
                TextInput::make('progress')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('color'),
                Toggle::make('is_critical_path')
                    ->required(),
            ]);
    }
}
