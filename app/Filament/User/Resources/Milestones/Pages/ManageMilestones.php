<?php

namespace App\Filament\User\Resources\Milestones\Pages;

use App\Filament\User\Resources\Milestones\MilestoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMilestones extends ManageRecords
{
    protected static string $resource = MilestoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateFormDataUsing(function (array $data): array {
                    $data['project_id'] = session('current_project_id');
                    return $data;
                }),
        ];
    }
}
