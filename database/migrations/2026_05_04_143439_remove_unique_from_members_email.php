<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Видалити унікальний індекс з поля email у таблиці members.
     * Причина: один email може використовуватись кількома членами (наприклад, літні люди
     * реєструються через email своїх дітей або родичів).
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }

    /**
     * Відновити унікальний індекс (якщо потрібен rollback).
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unique('email');
        });
    }
};
