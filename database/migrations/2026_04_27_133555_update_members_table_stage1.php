<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->string('member_number', 20)->unique()->nullable()->after('id');
            $table->string('birth_place')->nullable()->after('birth_date');
            $table->string('staatsangehoerigkeit')->nullable()->after('birth_place');
            $table->unsignedTinyInteger('familienangehoerige')->default(1)->after('staatsangehoerigkeit');
            $table->boolean('cenaze_fonu')->default(false)->after('familienangehoerige');
            $table->string('cenaze_fonu_nr', 50)->nullable()->after('cenaze_fonu');
            $table->boolean('gemeinderegister')->default(false)->after('cenaze_fonu_nr');
            $table->string('beruf')->nullable()->after('gemeinderegister');
            $table->string('heimatstadt')->nullable()->after('beruf');
            $table->enum('zahlungsart', ['barzahlung', 'lastschrift', 'dauerauftrag'])->default('barzahlung')->after('heimatstadt');
            $table->renameColumn('jahresbeitrag', 'monatsbeitrag');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->decimal('monatsbeitrag', 8, 2)->default(25.00)->change();
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn([
                'member_number',
                'birth_place',
                'staatsangehoerigkeit',
                'familienangehoerige',
                'cenaze_fonu',
                'cenaze_fonu_nr',
                'gemeinderegister',
                'beruf',
                'heimatstadt',
                'zahlungsart',
            ]);
            $table->renameColumn('monatsbeitrag', 'jahresbeitrag');
        });
    }
};
