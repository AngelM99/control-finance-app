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
        Schema::table('users', function (Blueprint $table) {
            // Campos para identificación y aprobación
            $table->string('dni', 20)->unique()->nullable()->after('email');
            $table->string('phone', 20)->nullable()->after('dni');
            $table->boolean('is_approved')->default(false)->after('phone');
            $table->timestamp('approved_at')->nullable()->after('is_approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');

            // Campos para OAuth
            $table->string('provider')->nullable()->after('password'); // google, manual
            $table->string('provider_id')->nullable()->after('provider');
            $table->string('avatar')->nullable()->after('provider_id');

            // Índices
            $table->index('dni');
            $table->index('is_approved');
            $table->index('provider');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropIndex(['dni']);
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['provider']);
            $table->dropColumn([
                'dni',
                'phone',
                'is_approved',
                'approved_at',
                'approved_by',
                'provider',
                'provider_id',
                'avatar'
            ]);
        });
    }
};
