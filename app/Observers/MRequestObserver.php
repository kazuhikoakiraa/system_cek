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
        $admins = User::role(['Super Admin', 'admin'])->get();
        Notification::send($admins, new MaintenanceRequestCreated($mRequest));
        
        // Notifikasi langsung ke Teknisi (tidak perlu approval)
        $teknisi = User::role(['Teknisi', 'Operator'])->get();
        if ($teknisi->isNotEmpty()) {
            Notification::send($teknisi, new MaintenanceRequestCreated($mRequest));
        }
        
        // Update status mesin ke maintenance
        if ($mRequest->mesin) {
            $mRequest->mesin->update(['status' => 'maintenance']);
        }
    }

    /**
     * Handle the MRequest "updated" event.
     */
    public function updated(MRequest $mRequest): void
    {
        // Check if status changed to completed
        if ($mRequest->isDirty('status') && $mRequest->status === 'completed') {
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

            // Update status mesin kembali ke aktif
            if ($mRequest->mesin) {
                $mRequest->mesin->update(['status' => 'aktif']);
            }
            
            // Notifikasi ke Admin & Super Admin bahwa pekerjaan selesai
            $admins = User::role(['Super Admin', 'admin'])->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new MaintenanceRequestApproved($mRequest));
            }
            
            // Notifikasi ke creator (yang buat request)
            $creator = $mRequest->creator;
            if ($creator) {
                $creator->notify(new MaintenanceRequestApproved($mRequest));
            }
        }
    }

    /**
     * Handle the MRequest "deleted" event.
     */
    public function deleted(MRequest $mRequest): void
    {
        //
    }

    /**
     * Handle the MRequest "restored" event.
     */
    public function restored(MRequest $mRequest): void
    {
        //
    }

    /**
     * Handle the MRequest "force deleted" event.
     */
    public function forceDeleted(MRequest $mRequest): void
    {
        //
    }
}
