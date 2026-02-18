<?php

namespace App\Filament\User\Resources\RoleResource\Pages;

use App\Filament\User\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        $canCreate = auth()->user()->hasProjectPermission('users.change_role');

        return [
            Actions\CreateAction::make()
                ->label('Nuovo Ruolo')
                ->visible($canCreate),
        ];
    }

    public function getTitle(): string
    {
        return 'Gestione Ruoli';
    }

    public function getSubheading(): ?string
    {
        return 'Configura i ruoli e i permessi per il progetto';
    }
}
