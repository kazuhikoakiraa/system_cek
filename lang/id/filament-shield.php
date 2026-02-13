<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Table Columns
    |--------------------------------------------------------------------------
    */

    'column.name' => 'Nama',
    'column.guard_name' => 'Nama Guard',
    'column.team' => 'Tim',
    'column.roles' => 'Role',
    'column.permissions' => 'Izin',
    'column.updated_at' => 'Diperbarui',

    /*
    |--------------------------------------------------------------------------
    | Form Fields
    |--------------------------------------------------------------------------
    */

    'field.name' => 'Nama',
    'field.guard_name' => 'Nama Guard',
    'field.permissions' => 'Izin',
    'field.team' => 'Tim',
    'field.team.placeholder' => 'Pilih tim ...',
    'field.select_all.name' => 'Pilih Semua',
    'field.select_all.message' => 'Mengaktifkan/Menonaktifkan semua Izin untuk role ini',

    /*
    |--------------------------------------------------------------------------
    | Navigation & Resource
    |--------------------------------------------------------------------------
    */

    'nav.group' => 'Filament Shield',
    'nav.role.label' => 'Role',
    'nav.role.icon' => 'heroicon-o-shield-check',
    'resource.label.role' => 'Role',
    'resource.label.roles' => 'Role',

    /*
    |--------------------------------------------------------------------------
    | Section & Tabs
    |--------------------------------------------------------------------------
    */

    'section' => 'Entitas',
    'resources' => 'Resources',
    'widgets' => 'Widget',
    'pages' => 'Halaman',
    'custom' => 'Izin Kustom',

    /*
    |--------------------------------------------------------------------------
    | Messages
    |--------------------------------------------------------------------------
    */

    'forbidden' => 'Anda tidak memiliki izin untuk mengakses',

    /*
    |--------------------------------------------------------------------------
    | Resource Permissions' Labels
    |--------------------------------------------------------------------------
    */

    'resource_permission_prefixes_labels' => [
        'view' => 'Lihat',
        'view_any' => 'Lihat Semua',
        'create' => 'Buat',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'delete_any' => 'Hapus Semua',
        'force_delete' => 'Hapus Permanen',
        'force_delete_any' => 'Hapus Permanen Semua',
        'restore' => 'Pulihkan',
        'replicate' => 'Replikasi',
        'reorder' => 'Urutkan Ulang',
        'restore_any' => 'Pulihkan Semua',
    ],
];
