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
        DB::table('members')
            ->select(['members.id', 'members.created_at'])
            ->leftJoin('member_audit_logs', function ($join): void {
                $join->on('member_audit_logs.member_id', '=', 'members.id')
                    ->where('member_audit_logs.event', '=', 'member_created');
            })
            ->whereNull('member_audit_logs.id')
            ->orderBy('members.id')
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
        DB::table('member_audit_logs')
            ->where('event', 'member_created')
            ->whereNull('actor_user_id')
            ->whereNull('actor_name')
            ->whereNull('changed_fields')
            ->whereNull('old_values')
            ->whereNull('new_values')
            ->delete();
    }
};
