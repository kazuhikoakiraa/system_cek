<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SparePartTransaction;
use Illuminate\Auth\Access\HandlesAuthorization;

class SparePartTransactionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_spare_part_transaction');
    }

    public function view(AuthUser $authUser, SparePartTransaction $sparePartTransaction): bool
    {
        return $authUser->can('view_spare_part_transaction');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_spare_part_transaction');
    }

    public function update(AuthUser $authUser, SparePartTransaction $sparePartTransaction): bool
    {
        return $authUser->can('update_spare_part_transaction');
    }

    public function delete(AuthUser $authUser, SparePartTransaction $sparePartTransaction): bool
    {
        return $authUser->can('delete_spare_part_transaction');
    }

    public function restore(AuthUser $authUser, SparePartTransaction $sparePartTransaction): bool
    {
        return $authUser->can('restore_spare_part_transaction');
    }

    public function forceDelete(AuthUser $authUser, SparePartTransaction $sparePartTransaction): bool
    {
        return $authUser->can('force_delete_spare_part_transaction');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_spare_part_transaction');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_spare_part_transaction');
    }

    public function replicate(AuthUser $authUser, SparePartTransaction $sparePartTransaction): bool
    {
        return $authUser->can('replicate_spare_part_transaction');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_spare_part_transaction');
    }

}