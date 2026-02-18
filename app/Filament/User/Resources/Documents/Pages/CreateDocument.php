<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    public function getTitle(): string
    {
        return 'Carica Documento';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // I campi is_folder, is_latest_version, version, uploaded_by
        // sono già gestiti dall'observer nel Document model.
        // Qui garantiamo che project_id sia impostato (BelongsToProject lo fa
        // ma lo esplicitiamo per sicurezza).
        $data['project_id'] = session('current_project_id');
        return $data;
    }
}
