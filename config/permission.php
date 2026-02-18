<?php

return [

    'models' => [
        'permission' => App\Models\Permission::class,
        'role' => App\Models\Role::class,
    ],

    'table_names' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles' => 'model_has_roles',
        'role_has_permissions' => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key' => null,
        'permission_pivot_key' => null,
        'model_morph_key' => 'model_id',
        'team_foreign_key' => 'project_id', // Usa project_id per multi-tenancy
    ],

    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,

    /*
     * Teams Feature - ABILITATO per multi-tenancy
     * I ruoli e permessi saranno scoped per project_id
     */
    'teams' => true,

    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => true,
    'display_role_in_exception' => true,

    /*
     * Wildcard permissions abilitati
     * Permette patterns come 'tasks.*' per tutti i permessi sui task
     */
    'enable_wildcard_permission' => true,

    'cache' => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key' => 'spatie.permission.cache',
        'store' => 'default',
    ],
];
