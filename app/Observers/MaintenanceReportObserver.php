<?php

namespace App\Observers;

use App\Models\MaintenanceReport;
use App\Models\SparePart;
use App\Models\User;
use App\Notifications\MaintenanceSelesaiNotification;
use Illuminate\Support\Facades\Notification;

class MaintenanceReportObserver
{
    /**
     * Handle the MaintenanceReport "created" event.
     */
    public function created(MaintenanceReport $maintenanceReport): void
    {
        // Set tanggal mulai when created
        if (!$maintenanceReport->tanggal_mulai) {
            $maintenanceReport->tanggal_mulai = now();
            $maintenanceReport->saveQuietly();
        }
    }

    /**
     * Handle the MaintenanceReport "updated" event.
     */
    public function updated(MaintenanceReport $maintenanceReport): void
    {
        // Auto update status berdasarkan foto yang diupload
        if ($maintenanceReport->isDirty('foto_sebelum') && $maintenanceReport->foto_sebelum && $maintenanceReport->status === 'pending') {
            $maintenanceReport->status = 'in_progress';
            $maintenanceReport->tanggal_mulai = now();
            $maintenanceReport->saveQuietly();
        }

        // Auto update status ke completed ketika foto sesudah diupload
        if ($maintenanceReport->isDirty('foto_sesudah') && $maintenanceReport->foto_sesudah && $maintenanceReport->status === 'in_progress') {
            $maintenanceReport->status = 'completed';
            $maintenanceReport->tanggal_selesai = now();
            $maintenanceReport->saveQuietly();

            // Kirim notifikasi ke Admin, Manager, dan Operator
            $this->sendMaintenanceSelesaiNotification($maintenanceReport);
        }
    }

    /**
     * Handle the MaintenanceReport "deleted" event.
     */
    public function deleted(MaintenanceReport $maintenanceReport): void
    {
        //
    }

    /**
     * Handle the MaintenanceReport "restored" event.
     */
    public function restored(MaintenanceReport $maintenanceReport): void
    {
        //
    }

    /**
     * Handle the MaintenanceReport "force deleted" event.
     */
    public function forceDeleted(MaintenanceReport $maintenanceReport): void
    {
        //
    }

    /**
     * Send notification when maintenance completed
     */
    private function sendMaintenanceSelesaiNotification(MaintenanceReport $maintenanceReport): void
    {
        $superAdmins = User::whereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))->get();

        // Admin dan Manager mendapat notifikasi
        $adminsAndManagers = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Super Admin', 'Admin']))
            ->get();

        // Operator yang melakukan pengecekan mendapat notifikasi
        $operator = $maintenanceReport->detailPengecekanMesin?->pengecekanMesin?->operator;
        
        $recipients = $adminsAndManagers->merge($superAdmins);
        if ($operator) {
            $recipients = $recipients->push($operator);
        }

        Notification::sendNow(
            $recipients->unique('id')->values(),
            new MaintenanceSelesaiNotification($maintenanceReport)
        );
    }
}
