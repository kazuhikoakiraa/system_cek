<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\PengecekanMesin;
use Illuminate\Auth\Access\HandlesAuthorization;

class PengecekanMesinPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_pengecekan_mesin');
    }

    public function view(AuthUser $authUser, PengecekanMesin $pengecekanMesin): bool
    {
        return $authUser->can('view_pengecekan_mesin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_pengecekan_mesin');
    }

    public function update(AuthUser $authUser, PengecekanMesin $pengecekanMesin): bool
    {
        return $authUser->can('update_pengecekan_mesin');
    }

    public function delete(AuthUser $authUser, PengecekanMesin $pengecekanMesin): bool
    {
        return $authUser->can('delete_pengecekan_mesin');
    }

    public function restore(AuthUser $authUser, PengecekanMesin $pengecekanMesin): bool
    {
        return $authUser->can('restore_pengecekan_mesin');
    }

    public function forceDelete(AuthUser $authUser, PengecekanMesin $pengecekanMesin): bool
    {
        return $authUser->can('force_delete_pengecekan_mesin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_pengecekan_mesin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_pengecekan_mesin');
    }

    public function replicate(AuthUser $authUser, PengecekanMesin $pengecekanMesin): bool
    {
        return $authUser->can('replicate_pengecekan_mesin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_pengecekan_mesin');
    }

}