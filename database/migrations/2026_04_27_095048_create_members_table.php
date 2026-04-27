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
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // Personendaten
            $table->string('full_name');
            $table->string('street');
            $table->string('city');
            $table->string('state');
            $table->string('postal_code', 10);
            $table->date('birth_date');
            $table->string('email')->unique();
            $table->string('phone', 30);

            // Mitgliedsbeitrag
            $table->decimal('jahresbeitrag', 8, 2)->default(36.00);

            // SEPA Bankdaten (verschlüsselt)
            $table->string('kontoinhaber');
            $table->text('iban');
            $table->text('bic')->nullable();
            $table->string('kreditinstitut')->nullable();

            // Unterschrift (Base64 PNG)
            $table->text('unterschrift');

            // Zustimmungen
            $table->boolean('sepa_zustimmung')->default(false);
            $table->boolean('dsgvo_zustimmung')->default(false);
            $table->timestamp('zustimmung_at')->nullable();

            // Status
            $table->enum('status', ['pending', 'active', 'inactive'])->default('pending');
            $table->text('admin_notiz')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
