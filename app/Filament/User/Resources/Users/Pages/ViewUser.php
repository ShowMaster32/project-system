<?php

namespace App\Filament\User\Resources\Users\Pages;

use App\Filament\User\Resources\Users\UserResource;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'User Details';
    }

    public function infolist(Schema $schema): Schema
    {
        $record = $this->getRecord();

        // Build a textual list of projects with role and membership state
        $memberships = $record->projects()
            ->select(['projects.id', 'projects.name', 'projects.code'])
            ->withPivot(['role', 'is_active'])
            ->get()
            ->map(function ($p) {
                $status = $p->pivot->is_active ? 'active' : 'inactive';
                return ($p->code ? ($p->code . ' – ') : '') . $p->name . " (role: {$p->pivot->role}, {$status})";
            })
            ->toArray();

        return $schema
            ->schema([
                Section::make('User')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('id')->label('User ID'),
                            TextEntry::make('name')->label('Username'),
                            TextEntry::make('email')->label('User Email'),
                            TextEntry::make('current_role')->label('User Role')->default(function () use ($record) {
                                $currentProjectId = session('current_project_id');
                                if (!$currentProjectId) return '—';
                                return $record->projects()
                                    ->where('project_id', $currentProjectId)
                                    ->first()?->pivot?->role ?? '—';
                            }),
                            TextEntry::make('access_level')->label('User Access Level')->default(function () use ($record) {
                                return $record->isGlobalAdmin() ? 'Global Admin' : ucfirst(session('current_user_role', 'user'));
                            }),
                            TextEntry::make('created_at')->label('Created at')->dateTime(),
                            TextEntry::make('updated_at')->label('Updated at')->dateTime(),
                        ]),
                    ]),
                Section::make('Projects')
                    ->schema([
                        KeyValueEntry::make('projects_list')
                            ->label('Projects Memberships')
                            ->default(function () use ($memberships) {
                                $arr = [];
                                foreach ($memberships as $i => $line) {
                                    $arr[(string) ($i + 1)] = $line;
                                }
                                return $arr;
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
