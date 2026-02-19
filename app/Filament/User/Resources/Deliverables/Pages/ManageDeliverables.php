<?php

namespace App\Filament\User\Resources\Deliverables\Pages;

use App\Filament\User\Resources\Deliverables\DeliverableResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageDeliverables extends ManageRecords
{
    protected static string $resource = DeliverableResource::class;

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
