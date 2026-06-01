<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Dedicated SEPA consent timestamp, separate from zustimmung_at
            // (application/DSGVO consent). Set when a member re-consents to a
            // Lastschrift bank-data change via /konto.
            $table->timestamp('sepa_zustimmung_at')->nullable()->after('sepa_zustimmung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('sepa_zustimmung_at');
        });
    }
};
