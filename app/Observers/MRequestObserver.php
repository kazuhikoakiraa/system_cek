<?php

namespace App\Observers;

use App\Models\MRequest;
use App\Models\MAudit;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreated;
use App\Notifications\MaintenanceRequestApproved;
use App\Notifications\MaintenanceRequestRejected;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;

class MRequestObserver
{
    /**
     * Handle the MRequest "created" event.
     */
    public function created(MRequest $mRequest): void
    {
        $superAdmins = User::whereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))->get();

        // Record audit trail
        MAudit::create([
            'mesin_id' => $mRequest->mesin_id,
            'm_request_id' => $mRequest->id,
            'action_type' => 'request_created',
            'user_id' => $mRequest->created_by,
            'deskripsi_perubahan' => "Request maintenance dibuat: {$mRequest->request_number}",
            'perubahan_data' => [
                'mesin' => $mRequest->mesin->nama_mesin ?? null,
                'urgency' => $mRequest->urgency_level,
                'deskripsi' => $mRequest->problema_deskripsi,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notifikasi ke Admin & Super Admin
        $admins = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Super Admin', 'Admin']))->get();
        Notification::sendNow(
            $admins->merge($superAdmins)->unique('id')->values(),
            new MaintenanceRequestCreated($mRequest)
        );
        
        // Notifikasi langsung ke Teknisi (tidak perlu approval)
        $teknisi = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Operator', 'Teknisi']))->get();
        if ($teknisi->isNotEmpty()) {
            Notification::sendNow(
                $teknisi->merge($superAdmins)->unique('id')->values(),
                new MaintenanceRequestCreated($mRequest)
            );
        }
        
        // Sinkronkan status mesin berdasarkan request aktif.
        MRequest::syncMachineStatus($mRequest->mesin_id);
    }

    /**
     * Handle the MRequest "updated" event.
     */
    public function updated(MRequest $mRequest): void
    {
        $superAdmins = User::whereHas('roles', fn ($query) => $query->where('name', 'Super Admin'))->get();

        // Check if status changed to completed
        if ($mRequest->wasChanged('status') && $mRequest->status === 'completed') {
            // Record audit trail
            MAudit::create([
                'mesin_id' => $mRequest->mesin_id,
                'm_request_id' => $mRequest->id,
                'action_type' => 'teknisi_completed',
                'user_id' => Auth::id(),
                'deskripsi_perubahan' => "Maintenance {$mRequest->request_number} selesai",
                'perubahan_data' => [
                    'status' => 'completed',
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notifikasi ke Admin & Super Admin bahwa pekerjaan selesai
            $admins = User::whereHas('roles', fn ($query) => $query->whereIn('name', ['Super Admin', 'Admin']))->get();
            if ($admins->isNotEmpty()) {
                Notification::sendNow(
                    $admins->merge($superAdmins)->unique('id')->values(),
                    new MaintenanceRequestApproved($mRequest)
                );
            }
            
            // Notifikasi ke creator (yang buat request)
            $creator = $mRequest->creator;
            if ($creator) {
                Notification::sendNow(
                    collect([$creator])->merge($superAdmins)->unique('id')->values(),
                    new MaintenanceRequestApproved($mRequest)
                );
            }
        }

        MRequest::syncMachineStatus($mRequest->mesin_id);
    }

    /**
     * Handle the MRequest "deleted" event.
     */
    public function deleted(MRequest $mRequest): void
    {
        MRequest::syncMachineStatus($mRequest->mesin_id);
    }

    /**
     * Handle the MRequest "restored" event.
     */
    public function restored(MRequest $mRequest): void
    {
        MRequest::syncMachineStatus($mRequest->mesin_id);
    }

    /**
     * Handle the MRequest "force deleted" event.
     */
    public function forceDeleted(MRequest $mRequest): void
    {
        MRequest::syncMachineStatus($mRequest->mesin_id);
    }
}
