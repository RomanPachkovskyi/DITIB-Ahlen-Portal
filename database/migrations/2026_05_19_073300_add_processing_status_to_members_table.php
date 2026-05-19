<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE members MODIFY status ENUM('pending', 'processing', 'active', 'inactive') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::transaction(function (): void {
            DB::table('members')
                ->where('status', 'processing')
                ->update(['status' => 'pending']);

            DB::statement("ALTER TABLE members MODIFY status ENUM('pending', 'active', 'inactive') NOT NULL DEFAULT 'pending'");
        });
    }
};
