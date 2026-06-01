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
        Schema::create('member_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_type', 30);
            $table->string('actor_name')->nullable();
            $table->string('event', 80);
            $table->string('description');
            $table->json('changed_fields')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['member_id', 'created_at']);
            $table->index(['actor_type', 'created_at']);
            $table->index(['event', 'created_at']);
        });

        DB::table('members')
            ->select(['id', 'created_at'])
            ->orderBy('id')
            ->chunk(500, function ($members): void {
                $rows = $members->map(fn ($member): array => [
                    'member_id' => $member->id,
                    'actor_user_id' => null,
                    'actor_type' => 'client',
                    'actor_name' => null,
                    'event' => 'member_created',
                    'description' => 'Account erstellt',
                    'changed_fields' => null,
                    'old_values' => null,
                    'new_values' => null,
                    'created_at' => $member->created_at,
                ])->all();

                if ($rows !== []) {
                    DB::table('member_audit_logs')->insert($rows);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_audit_logs');
    }
};
