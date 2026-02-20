<?php

namespace App\Filament\User\Resources\Documents;

use App\Filament\User\Resources\Documents\Pages\CreateDocument;
use App\Filament\User\Resources\Documents\Pages\ListDocuments;
use App\Filament\User\Resources\Documents\Pages\ViewDocument;
use App\Models\Document;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Archivio';

    protected static ?int $navigationSort = 6;

    // ──── Form ────────────────────────────────────────────────────────────

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            FileUpload::make('file_path')
                ->label('File')
                ->disk('documents')
                ->directory(fn () => (string) (session('current_project_id') ?? 'general'))
                ->maxSize(51200)   // 50 MB
                ->required()
                ->columnSpanFull()
                ->acceptedFileTypes([
                    'application/pdf',
                    'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/vnd.ms-powerpoint',
                    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'text/plain', 'text/csv',
                    'application/zip', 'application/x-zip-compressed',
                ]),

            TextInput::make('name')
                ->label('Nome documento')
                ->required()
                ->maxLength(255)
                ->placeholder('es. Report mensile Gennaio 2026'),

            Select::make('category')
                ->label('Categoria')
                ->options(Document::CATEGORIES)
                ->required()
                ->default('other')
                ->native(false),
        ]);
    }

    // ──── Table ───────────────────────────────────────────────────────────

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category_label')
                    ->label('Categoria')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Deliverable'  => 'success',
                        'Report'       => 'info',
                        'Contratto'    => 'danger',
                        'Presentazione'=> 'warning',
                        'Verbale'      => 'gray',
                        default        => 'gray',
                    }),

                TextColumn::make('mime_type')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($record) => self::mimeLabel($record->mime_type ?? ''))
                    ->badge()
                    ->color('gray'),

                TextColumn::make('formatted_size')
                    ->label('Dimensione')
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy('size_bytes', $direction)),

                TextColumn::make('uploader.name')
                    ->label('Caricato da')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Data')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('category')
                    ->label('Categoria')
                    ->options(Document::CATEGORIES),
            ])
            ->recordActions([
                ViewAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('documents.view')),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->hasProjectPermission('documents.delete')),
            ]);
    }

    // ──── Pages ───────────────────────────────────────────────────────────

    public static function getPages(): array
    {
        return [
            'index'  => ListDocuments::route('/'),
            'create' => CreateDocument::route('/create'),
            'view'   => ViewDocument::route('/{record}'),
        ];
    }

    // ──── Permessi ────────────────────────────────────────────────────────

    public static function canCreate(): bool
    {
        return auth()->user()?->hasProjectPermission('documents.upload') ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasProjectPermission('documents.edit') ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasProjectPermission('documents.delete') ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasProjectPermission('documents.view') ?? false;
    }

    // ──── Helpers privati ─────────────────────────────────────────────────

    private static function mimeLabel(string $mime): string
    {
        return match (true) {
            str_starts_with($mime, 'image/') => 'Immagine',
            $mime === 'application/pdf'       => 'PDF',
            in_array($mime, [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ])                               => 'Word',
            in_array($mime, [
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])                               => 'Excel',
            in_array($mime, [
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ])                               => 'PowerPoint',
            str_contains($mime, 'zip')       => 'ZIP',
            $mime === 'text/plain'            => 'Testo',
            $mime === 'text/csv'             => 'CSV',
            default                          => 'File',
        };
    }
}
