<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        Schema::create('member_number_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->unsignedBigInteger('next_number');
            $table->timestamps();
        });

        $lastUsedNumber = DB::table('members')
            ->whereNotNull('member_number')
            ->pluck('member_number')
            ->map(function (string $memberNumber): int {
                preg_match('/(\d+)$/', $memberNumber, $matches);

                return isset($matches[1]) ? (int) $matches[1] : 0;
            })
            ->max() ?? 0;

        DB::table('member_number_sequences')->insert([
            'name' => 'members',
            'next_number' => $lastUsedNumber + 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_number_sequences');

        Schema::table('members', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
