<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Convert historical pengecekan timestamps that were stored in UTC
     * into WIB (Asia/Jakarta) by adding 7 hours.
     *
     * Note: We intentionally skip records stored at 00:00:00 (used for "tidak_dicek" placeholders).
     */
    public function up(): void
    {
        DB::statement("
            UPDATE pengecekan_mesins
            SET tanggal_pengecekan = DATE_ADD(tanggal_pengecekan, INTERVAL 7 HOUR)
            WHERE status IN ('selesai', 'dalam_proses')
              AND TIME(tanggal_pengecekan) <> '00:00:00'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE pengecekan_mesins
            SET tanggal_pengecekan = DATE_SUB(tanggal_pengecekan, INTERVAL 7 HOUR)
            WHERE status IN ('selesai', 'dalam_proses')
              AND TIME(tanggal_pengecekan) <> '00:00:00'
        ");
    }
};
