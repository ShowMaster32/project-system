# 🔐 Spatie Laravel-Permission - Guida Installazione

## Panoramica
Pacchetto completo per integrare Spatie Laravel-Permission nel tuo Project Management System multi-tenant.

## Struttura Ruoli

| Ruolo | Scope | Level | Descrizione |
|-------|-------|-------|-------------|
| `super_admin` | Globale | 100 | Accesso completo a tutto il sistema |
| `project_admin` | Progetto | 90 | Amministratore del singolo progetto (equivale a 'admin' attuale) |
| `coordinator` | Progetto | 70 | Coordina work packages e task |
| `wp_leader` | Progetto | 50 | Gestisce work packages assegnati |
| `task_leader` | Progetto | 40 | Gestisce task assegnati |
| `team_member` | Progetto | 20 | Membro operativo del team (equivale a 'user' attuale) |
| `viewer` | Progetto | 10 | Solo visualizzazione |

---

## 📦 Step 1: Installa Spatie Permission

```bash
composer require spatie/laravel-permission
```

---

## 📦 Step 2: Copia i File

```
spatie-permission-package/
├── app/
│   ├── Models/
│   │   ├── Role.php                    → app/Models/Role.php
│   │   └── Permission.php              → app/Models/Permission.php
│   ├── Traits/
│   │   └── HasProjectPermissions.php   → app/Traits/HasProjectPermissions.php
│   └── Filament/User/Resources/
│       ├── RoleResource.php            → app/Filament/User/Resources/RoleResource.php
│       └── RoleResource/Pages/         → app/Filament/User/Resources/RoleResource/Pages/
├── config/
│   └── permission.php                  → config/permission.php
└── database/
    ├── migrations/
    │   └── 2025_01_31_...php           → database/migrations/
    └── seeders/
        └── RolesAndPermissionsSeeder.php → database/seeders/
```

---

## 📦 Step 3: Aggiorna User Model

In `app/Models/User.php` aggiungi i trait:

```php
use Spatie\Permission\Traits\HasRoles;
use App\Traits\HasProjectPermissions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, HasProjectPermissions;
    // ... resto del codice ...
}
```

---

## 📦 Step 4: Aggiorna AppServiceProvider

In `app/Providers/AppServiceProvider.php`:

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    // Super admin bypass
    Gate::before(function ($user, $ability) {
        if ($user->hasRole('super_admin')) {
            return true;
        }
    });
}
```

---

## 📦 Step 5: Esegui Migrazioni e Seeder

```bash
php artisan migrate
php artisan db:seed --class=RolesAndPermissionsSeeder
php artisan permission:cache-reset
```

---

## 🧪 Test Rapido

```bash
php artisan tinker
```

```php
$user = User::first();
$user->assignRole('super_admin');
session(['current_project_id' => 1]);
$user->hasProjectPermission('tasks.create'); // true
```

---

## 📝 Uso nelle Resource Filament

```php
public static function canCreate(): bool
{
    return auth()->user()->hasProjectPermission('work_packages.create');
}
```

---

## 🔄 Compatibilità

Il trait `HasProjectPermissions` mantiene compatibilità con i ruoli esistenti nel pivot `project_user`.
