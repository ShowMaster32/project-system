<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    public function getTitle(): string
    {
        $project = session('current_project_name', 'Progetto');
        return "Archivio Documentale — {$project}";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Carica documento')
                ->visible(fn () => auth()->user()?->hasProjectPermission('documents.upload')),
        ];
    }
}
