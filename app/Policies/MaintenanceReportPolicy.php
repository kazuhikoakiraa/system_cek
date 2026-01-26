<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MaintenanceReport;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceReportPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any_maintenance_report');
    }

    public function view(AuthUser $authUser, MaintenanceReport $maintenanceReport): bool
    {
        return $authUser->can('view_maintenance_report');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create_maintenance_report');
    }

    public function update(AuthUser $authUser, MaintenanceReport $maintenanceReport): bool
    {
        return $authUser->can('update_maintenance_report');
    }

    public function delete(AuthUser $authUser, MaintenanceReport $maintenanceReport): bool
    {
        return $authUser->can('delete_maintenance_report');
    }

    public function restore(AuthUser $authUser, MaintenanceReport $maintenanceReport): bool
    {
        return $authUser->can('restore_maintenance_report');
    }

    public function forceDelete(AuthUser $authUser, MaintenanceReport $maintenanceReport): bool
    {
        return $authUser->can('force_delete_maintenance_report');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any_maintenance_report');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any_maintenance_report');
    }

    public function replicate(AuthUser $authUser, MaintenanceReport $maintenanceReport): bool
    {
        return $authUser->can('replicate_maintenance_report');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder_maintenance_report');
    }

}