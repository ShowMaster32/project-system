<?php

namespace App\Filament\User\Resources\Documents\Pages;

use App\Filament\User\Resources\Documents\DocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewDocument extends ViewRecord
{
    protected static string $resource = DocumentResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        $canDelete = auth()->user()?->hasProjectPermission('documents.delete');
        $canDownload = auth()->user()?->hasProjectPermission('documents.download');

        $actions = [];

        // Bottone Download
        if ($canDownload && $this->record->file_path) {
            $actions[] = \Filament\Actions\Action::make('download')
                ->label('Scarica')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('documents.download', $this->record))
                ->openUrlInNewTab();
        }

        // Bottone Elimina
        if ($canDelete) {
            $actions[] = DeleteAction::make();
        }

        return $actions;
    }

    public function infolist(Schema $schema): Schema
    {
        $record = $this->record;
        $canDownload = auth()->user()?->hasProjectPermission('documents.download');

        return $schema->schema([

            // ── Anteprima (immagini e PDF) ──────────────────────────────
            Section::make('Anteprima')
                ->schema([
                    // HTML grezzo per gestire sia img che iframe
                    TextEntry::make('preview_html')
                        ->label('')
                        ->default(function () use ($record, $canDownload) {
                            if (! $canDownload || ! $record->file_path) {
                                return '*(Nessuna anteprima disponibile)*';
                            }
                            $url = route('documents.preview', $record);
                            if ($record->is_image) {
                                return '<img src="' . $url . '" alt="' . e($record->name) . '" class="max-w-full max-h-96 rounded-lg shadow">';
                            }
                            if ($record->is_pdf) {
                                return '<iframe src="' . $url . '" class="w-full rounded-lg shadow" style="height:600px;" frameborder="0"></iframe>';
                            }
                            return '*(Anteprima non disponibile per questo tipo di file)*';
                        })
                        ->html()
                        ->columnSpanFull(),
                ])
                ->visible(fn () => $record->is_image || $record->is_pdf)
                ->collapsible(),

            // ── Dettagli documento ──────────────────────────────────────
            Section::make('Dettagli')
                ->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')
                            ->label('Nome'),

                        TextEntry::make('category_label')
                            ->label('Categoria')
                            ->badge(),

                        TextEntry::make('mime_type')
                            ->label('Tipo file')
                            ->formatStateUsing(fn ($record) => $record->mime_type ?? '—'),

                        TextEntry::make('formatted_size')
                            ->label('Dimensione'),

                        TextEntry::make('uploader.name')
                            ->label('Caricato da'),

                        TextEntry::make('created_at')
                            ->label('Data caricamento')
                            ->dateTime('d/m/Y H:i'),
                    ]),
                ]),

        ]);
    }
}
