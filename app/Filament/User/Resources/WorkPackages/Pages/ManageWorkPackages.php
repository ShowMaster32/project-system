<?php

namespace App\Filament\User\Resources\WorkPackages\Pages;

use App\Filament\User\Resources\WorkPackages\WorkPackageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageWorkPackages extends ManageRecords
{
    protected static string $resource = WorkPackageResource::class;

    protected function getHeaderActions(): array
    {
        $role = session('current_user_role');
        $canCreate = in_array($role, ['admin', 'coordinator']);

        return [
            CreateAction::make()->visible($canCreate),
        ];
    }
}
