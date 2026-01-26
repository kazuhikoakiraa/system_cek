<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Mesin;
use Illuminate\Auth\Access\HandlesAuthorization;

class MesinPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_mesin');
    }

    public function view(AuthUser $authUser, Mesin $mesin): bool
    {
        return $authUser->can('view_mesin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_mesin');
    }

    public function update(AuthUser $authUser, Mesin $mesin): bool
    {
        return $authUser->can('update_mesin');
    }

    public function delete(AuthUser $authUser, Mesin $mesin): bool
    {
        return $authUser->can('delete_mesin');
    }

    public function restore(AuthUser $authUser, Mesin $mesin): bool
    {
        return $authUser->can('restore_mesin');
    }

    public function forceDelete(AuthUser $authUser, Mesin $mesin): bool
    {
        return $authUser->can('force_delete_mesin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_mesin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_mesin');
    }

    public function replicate(AuthUser $authUser, Mesin $mesin): bool
    {
        return $authUser->can('replicate_mesin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_mesin');
    }

}