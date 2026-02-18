<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentDownloadController extends Controller
{
    /**
     * Verifica che l'utente possa accedere al documento.
     */
    private function authorizeAccess(Document $document): void
    {
        $user = auth()->user();

        // Deve avere il permesso di download
        abort_unless(
            $user?->hasProjectPermission('documents.download'),
            403,
            'Non hai i permessi per scaricare questo documento.'
        );

        // Il documento deve appartenere al progetto corrente
        $currentProjectId = session('current_project_id');
        abort_unless(
            $document->project_id == $currentProjectId,
            403,
            'Documento non appartenente al progetto corrente.'
        );
    }

    /**
     * Scarica il file (attachment).
     */
    public function download(Document $document)
    {
        $this->authorizeAccess($document);

        abort_unless(
            Storage::disk('documents')->exists($document->file_path),
            404,
            'File non trovato nello storage.'
        );

        return Storage::disk('documents')
            ->download($document->file_path, $document->name);
    }

    /**
     * Restituisce il file inline (per anteprime immagini/PDF nel browser).
     */
    public function preview(Document $document)
    {
        $this->authorizeAccess($document);

        abort_unless(
            Storage::disk('documents')->exists($document->file_path),
            404,
            'File non trovato nello storage.'
        );

        abort_unless(
            $document->is_image || $document->is_pdf,
            403,
            'Tipo di file non supportato per anteprima inline.'
        );

        return Storage::disk('documents')
            ->response($document->file_path, $document->name, [
                'Content-Type'        => $document->mime_type,
                'Content-Disposition' => 'inline; filename="' . $document->name . '"',
            ]);
    }
}
