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
        Schema::table('lenders', function (Blueprint $table) {
            // Eliminar la restricción única de document_id
            $table->dropUnique('lenders_document_id_unique');

            // Agregar una restricción única compuesta de user_id y document_id
            // Esto permite que diferentes usuarios registren al mismo prestamista (mismo DNI)
            // pero evita que un usuario registre el mismo DNI dos veces
            $table->unique(['user_id', 'document_id'], 'lenders_user_document_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lenders', function (Blueprint $table) {
            // Eliminar la restricción única compuesta
            $table->dropUnique('lenders_user_document_unique');

            // Restaurar la restricción única simple de document_id
            $table->unique('document_id');
        });
    }
};
