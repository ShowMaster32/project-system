<?php

namespace App\Filament\User\Resources\WorkPackages\Pages;

use App\Filament\User\Resources\WorkPackages\WorkPackageResource;
use Filament\Resources\Pages\ListRecords;

class ListWorkPackages extends ListRecords
{
    protected static string $resource = WorkPackageResource::class;
}
