<?php

namespace App\Filament\User\Resources\RoleResource\Pages;

use App\Filament\User\Resources\RoleResource;
use Filament\Actions;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    public function getTitle(): string
    {
        return 'Dettagli Ruolo: ' . ucfirst(str_replace('_', ' ', $this->record->name));
    }

    protected function getHeaderActions(): array
    {
        $canEdit = auth()->user()->hasProjectPermission('users.change_role');
        $isSystemRole = in_array($this->record->name, ['super_admin', 'project_admin', 'coordinator', 'wp_leader', 'task_leader', 'team_member', 'viewer']);

        return [
            Actions\EditAction::make()
                ->visible($canEdit && !$isSystemRole),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informazioni Ruolo')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('name')
                                    ->label('Nome Ruolo')
                                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                                TextEntry::make('level')
                                    ->label('Livello Gerarchia')
                                    ->badge()
                                    ->color(fn (int $state): string => match(true) {
                                        $state >= 90 => 'danger',
                                        $state >= 70 => 'warning',
                                        $state >= 40 => 'info',
                                        default => 'gray',
                                    }),

                                TextEntry::make('description')
                                    ->label('Descrizione')
                                    ->default('Nessuna descrizione')
                                    ->columnSpanFull(),

                                TextEntry::make('is_default')
                                    ->label('Ruolo Default')
                                    ->badge()
                                    ->formatStateUsing(fn (bool $state) => $state ? 'Sì' : 'No')
                                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),

                                TextEntry::make('guard_name')
                                    ->label('Guard'),

                                TextEntry::make('created_at')
                                    ->label('Creato il')
                                    ->dateTime('d/m/Y H:i'),

                                TextEntry::make('updated_at')
                                    ->label('Aggiornato il')
                                    ->dateTime('d/m/Y H:i'),
                            ]),
                    ]),

                Section::make('Permessi Assegnati')
                    ->schema([
                        TextEntry::make('permissions_list')
                            ->label('')
                            ->default(function () {
                                $permissions = $this->record->permissions()
                                    ->orderBy('group')
                                    ->orderBy('name')
                                    ->get();

                                if ($permissions->isEmpty()) {
                                    return 'Nessun permesso assegnato';
                                }

                                return $permissions
                                    ->groupBy('group')
                                    ->map(function ($perms, $group) {
                                        $groupName = ucfirst($group ?? 'Altri');
                                        $permList = $perms->map(fn ($p) => "• " . ($p->description ?? $p->name))->join("\n");
                                        return "**{$groupName}**\n{$permList}";
                                    })
                                    ->join("\n\n");
                            })
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('Utenti con questo Ruolo')
                    ->schema([
                        TextEntry::make('users_list')
                            ->label('')
                            ->default(function () {
                                $users = $this->record->users()->take(10)->get();
                                $total = $this->record->users()->count();

                                if ($users->isEmpty()) {
                                    return 'Nessun utente con questo ruolo';
                                }

                                $list = $users->map(fn ($u) => "• {$u->name} ({$u->email})")->join("\n");
                                
                                if ($total > 10) {
                                    $list .= "\n\n*...e altri " . ($total - 10) . " utenti*";
                                }

                                return $list;
                            })
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
