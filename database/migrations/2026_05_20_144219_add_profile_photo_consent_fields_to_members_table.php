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
            $table->boolean('profile_photo_zustimmung')->default(false)->after('profile_photo_uploaded_at');
            $table->timestamp('profile_photo_zustimmung_at')->nullable()->after('profile_photo_zustimmung');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'profile_photo_zustimmung',
                'profile_photo_zustimmung_at',
            ]);
        });
    }
};
