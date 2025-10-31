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
        Schema::create('otp_tokens', function (Blueprint $table) {
            $table->id();

            // DNI del usuario que solicita el OTP
            $table->string('dni', 20);

            // Token OTP (6 dígitos)
            $table->string('token', 6);

            // Expiración y uso
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamp('used_at')->nullable();

            // Seguridad
            $table->string('ip_address', 45)->nullable(); // IPv4 o IPv6
            $table->integer('attempts')->default(0); // Intentos fallidos de validación
            $table->timestamp('last_attempt_at')->nullable();

            // Metadata
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Índices
            $table->index('dni');
            $table->index('token');
            $table->index('expires_at');
            $table->index('used');
            $table->index(['dni', 'used']);
            $table->index(['token', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otp_tokens');
    }
};
