<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\DaftarPengecekan;
use Illuminate\Auth\Access\HandlesAuthorization;

class DaftarPengecekanPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_daftar_pengecekan');
    }

    public function view(AuthUser $authUser, DaftarPengecekan $daftarPengecekan): bool
    {
        return $authUser->can('view_daftar_pengecekan');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_daftar_pengecekan');
    }

    public function update(AuthUser $authUser, DaftarPengecekan $daftarPengecekan): bool
    {
        return $authUser->can('update_daftar_pengecekan');
    }

    public function delete(AuthUser $authUser, DaftarPengecekan $daftarPengecekan): bool
    {
        return $authUser->can('delete_daftar_pengecekan');
    }

    public function restore(AuthUser $authUser, DaftarPengecekan $daftarPengecekan): bool
    {
        return $authUser->can('restore_daftar_pengecekan');
    }

    public function forceDelete(AuthUser $authUser, DaftarPengecekan $daftarPengecekan): bool
    {
        return $authUser->can('force_delete_daftar_pengecekan');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_daftar_pengecekan');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_daftar_pengecekan');
    }

    public function replicate(AuthUser $authUser, DaftarPengecekan $daftarPengecekan): bool
    {
        return $authUser->can('replicate_daftar_pengecekan');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_daftar_pengecekan');
    }

}