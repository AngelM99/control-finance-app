<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class CleanUserData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:clean {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia todos los datos de un usuario específico (transacciones, cuotas, productos financieros, prestamistas)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Buscar el usuario
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ No se encontró el usuario con email: {$email}");
            return 1;
        }

        $this->info("🔍 Usuario encontrado: {$user->name} ({$user->email})");
        $this->info("ID: {$user->id}");
        $this->newLine();

        // Confirmar
        if (!$this->confirm('¿Estás seguro de que deseas eliminar TODOS los datos de este usuario?')) {
            $this->info('Operación cancelada.');
            return 0;
        }

        $this->info('🗑️  Iniciando limpieza de datos...');
        $this->newLine();

        DB::beginTransaction();

        try {
            $userId = $user->id;

            // 1. Limpiar transacciones
            $transactionsCount = DB::table('transactions')->where('user_id', $userId)->count();
            DB::table('transactions')->where('user_id', $userId)->delete();
            $this->info("✅ Transacciones eliminadas: {$transactionsCount}");

            // 2. Limpiar installments (cuotas)
            $installmentsCount = DB::table('installments')->where('user_id', $userId)->count();
            DB::table('installments')->where('user_id', $userId)->delete();
            $this->info("✅ Planes de cuotas eliminados: {$installmentsCount}");

            // 3. Limpiar productos financieros
            $productsCount = DB::table('financial_products')->where('user_id', $userId)->count();
            DB::table('financial_products')->where('user_id', $userId)->delete();
            $this->info("✅ Productos financieros eliminados: {$productsCount}");

            // 4. Limpiar prestamistas (lenders)
            $lendersCount = DB::table('lenders')->where('user_id', $userId)->count();
            DB::table('lenders')->where('user_id', $userId)->delete();
            $this->info("✅ Prestamistas eliminados: {$lendersCount}");

            DB::commit();

            $this->newLine();
            $this->info('🎉 ¡Limpieza completada exitosamente!');
            $this->info("El usuario {$user->name} ahora está como recién creado.");
            $this->newLine();

            // Mostrar resumen
            $this->table(
                ['Tipo de Dato', 'Cantidad Eliminada'],
                [
                    ['Transacciones', $transactionsCount],
                    ['Planes de Cuotas', $installmentsCount],
                    ['Productos Financieros', $productsCount],
                    ['Prestamistas', $lendersCount],
                ]
            );

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error al limpiar los datos: ' . $e->getMessage());
            return 1;
        }
    }
}
