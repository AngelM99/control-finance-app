<?php

namespace App\Livewire\FinancialProducts;

use App\Models\FinancialProduct;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Productos Financieros - Control Finance')]
class ProductList extends Component
{
    public function deleteProduct($productId)
    {
        try {
            $product = FinancialProduct::where('id', $productId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            // Verificar si tiene transacciones asociadas
            $transactionsCount = $product->transactions()->count();
            if ($transactionsCount > 0) {
                session()->flash('error', "No se puede eliminar este producto porque tiene {$transactionsCount} transacción(es) asociada(s). Primero debes eliminar las transacciones.");
                return;
            }

            // Verificar si tiene cuotas asociadas
            $installmentsCount = $product->installments()->count();
            if ($installmentsCount > 0) {
                session()->flash('error', "No se puede eliminar este producto porque tiene {$installmentsCount} plan(es) de cuotas activo(s). Primero debes eliminar o completar las cuotas.");
                return;
            }

            $product->delete();

            session()->flash('success', 'Producto eliminado exitosamente.');

            // Refrescar el componente
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    public function toggleStatus($productId)
    {
        try {
            $product = FinancialProduct::where('id', $productId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $product->update(['is_active' => !$product->is_active]);

            session()->flash('success', 'Estado del producto actualizado.');

            // Refrescar el componente
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    public function render()
    {
        // DataTables maneja la paginación, búsqueda y filtrado
        $products = FinancialProduct::where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('livewire.financial-products.product-list', [
            'products' => $products
        ]);
    }
}
