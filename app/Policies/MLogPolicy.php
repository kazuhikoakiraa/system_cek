<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class MLogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_m_log');
    }

    public function view(AuthUser $authUser, MLog $mLog): bool
    {
        return $authUser->can('view_m_log');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_m_log');
    }

    public function update(AuthUser $authUser, MLog $mLog): bool
    {
        return $authUser->can('update_m_log');
    }

    public function delete(AuthUser $authUser, MLog $mLog): bool
    {
        return $authUser->can('delete_m_log');
    }

    public function restore(AuthUser $authUser, MLog $mLog): bool
    {
        return $authUser->can('restore_m_log');
    }

    public function forceDelete(AuthUser $authUser, MLog $mLog): bool
    {
        return $authUser->can('force_delete_m_log');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_m_log');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_m_log');
    }

    public function replicate(AuthUser $authUser, MLog $mLog): bool
    {
        return $authUser->can('replicate_m_log');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_m_log');
    }

}