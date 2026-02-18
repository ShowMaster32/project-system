#!/bin/bash

# =============================================================================
# Script di Installazione Spatie Laravel-Permission
# Per Project Management System Multi-Tenant
# =============================================================================

echo "🔐 Installazione Spatie Laravel-Permission"
echo "==========================================="
echo ""

# Verifica che siamo nella root del progetto Laravel
if [ ! -f "artisan" ]; then
    echo "❌ Errore: Esegui questo script dalla root del progetto Laravel"
    exit 1
fi

# Verifica che il pacchetto sorgente esista
PACKAGE_DIR="./spatie-permission-package"
if [ ! -d "$PACKAGE_DIR" ]; then
    echo "❌ Errore: Directory $PACKAGE_DIR non trovata"
    echo "   Assicurati di aver estratto il pacchetto nella root del progetto"
    exit 1
fi

echo "📦 Step 1: Installazione Spatie Permission via Composer..."
composer require spatie/laravel-permission

echo ""
echo "📁 Step 2: Copia dei file..."

# Crea le directory necessarie
mkdir -p app/Models
mkdir -p app/Traits
mkdir -p app/Providers
mkdir -p app/Filament/User/Resources/RoleResource/Pages
mkdir -p config
mkdir -p database/migrations
mkdir -p database/seeders

# Copia i file
cp -v "$PACKAGE_DIR/config/permission.php" config/
cp -v "$PACKAGE_DIR/database/migrations/"*.php database/migrations/
cp -v "$PACKAGE_DIR/database/seeders/RolesAndPermissionsSeeder.php" database/seeders/
cp -v "$PACKAGE_DIR/app/Models/Role.php" app/Models/
cp -v "$PACKAGE_DIR/app/Models/Permission.php" app/Models/
cp -v "$PACKAGE_DIR/app/Models/User.php" app/Models/
cp -v "$PACKAGE_DIR/app/Traits/HasProjectPermissions.php" app/Traits/
cp -v "$PACKAGE_DIR/app/Providers/AppServiceProvider.php" app/Providers/
cp -v "$PACKAGE_DIR/app/Filament/User/Resources/RoleResource.php" app/Filament/User/Resources/
cp -v "$PACKAGE_DIR/app/Filament/User/Resources/RoleResource/Pages/"*.php app/Filament/User/Resources/RoleResource/Pages/

echo ""
echo "🗄️ Step 3: Esecuzione migrazioni..."
php artisan migrate

echo ""
echo "🌱 Step 4: Esecuzione seeder..."
php artisan db:seed --class=RolesAndPermissionsSeeder

echo ""
echo "🧹 Step 5: Pulizia cache..."
php artisan config:clear
php artisan cache:clear
php artisan permission:cache-reset

echo ""
echo "✅ Installazione completata!"
echo ""
echo "📋 Prossimi passi:"
echo "   1. Verifica che tutto funzioni: php artisan tinker"
echo "   2. Assegna ruoli agli utenti esistenti"
echo "   3. Aggiorna le tue Resource Filament per usare hasProjectPermission()"
echo ""
echo "📖 Consulta INSTALL.md per la documentazione completa"
