<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SparePart;
use Illuminate\Auth\Access\HandlesAuthorization;

class SparePartPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_spare_part');
    }

    public function view(AuthUser $authUser, SparePart $sparePart): bool
    {
        return $authUser->can('view_spare_part');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_spare_part');
    }

    public function update(AuthUser $authUser, SparePart $sparePart): bool
    {
        return $authUser->can('update_spare_part');
    }

    public function delete(AuthUser $authUser, SparePart $sparePart): bool
    {
        return $authUser->can('delete_spare_part');
    }

    public function restore(AuthUser $authUser, SparePart $sparePart): bool
    {
        return $authUser->can('restore_spare_part');
    }

    public function forceDelete(AuthUser $authUser, SparePart $sparePart): bool
    {
        return $authUser->can('force_delete_spare_part');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_spare_part');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_spare_part');
    }

    public function replicate(AuthUser $authUser, SparePart $sparePart): bool
    {
        return $authUser->can('replicate_spare_part');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_spare_part');
    }

}