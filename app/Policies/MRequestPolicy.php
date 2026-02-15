<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MRequest;
use Illuminate\Auth\Access\HandlesAuthorization;

class MRequestPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_m_request');
    }

    public function view(AuthUser $authUser, MRequest $mRequest): bool
    {
        return $authUser->can('view_m_request');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_m_request');
    }

    public function update(AuthUser $authUser, MRequest $mRequest): bool
    {
        return $authUser->can('update_m_request');
    }

    public function delete(AuthUser $authUser, MRequest $mRequest): bool
    {
        return $authUser->can('delete_m_request');
    }

    public function restore(AuthUser $authUser, MRequest $mRequest): bool
    {
        return $authUser->can('restore_m_request');
    }

    public function forceDelete(AuthUser $authUser, MRequest $mRequest): bool
    {
        return $authUser->can('force_delete_m_request');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_m_request');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_m_request');
    }

    public function replicate(AuthUser $authUser, MRequest $mRequest): bool
    {
        return $authUser->can('replicate_m_request');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_m_request');
    }

}