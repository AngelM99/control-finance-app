<div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Productos Financieros</h6>
                        @can('create financial products')
                            <a href="{{ route('products.create') }}" class="btn btn-sm bg-gradient-primary mb-0" wire:navigate>
                                <i class="fas fa-plus me-2"></i>Nuevo Producto
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($products->count() > 0)
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Límite</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Saldo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-credit-card text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $product->account_number }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ ucfirst(str_replace('_', ' ', $product->product_type)) }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0">${{ number_format($product->credit_limit / 100, 2) }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">${{ number_format($product->current_balance / 100, 2) }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm {{ $product->is_active ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    @can('edit financial products')
                                                        <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-primary mb-0" wire:navigate>
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete financial products')
                                                        <button wire:click="delete({{ $product->id }})"
                                                                wire:confirm="¿Está seguro de eliminar este producto?"
                                                                class="btn btn-sm btn-outline-danger mb-0">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 mt-3">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-3">No tienes productos financieros registrados</p>
                            @can('create financial products')
                                <a href="{{ route('products.create') }}" class="btn bg-gradient-primary" wire:navigate>
                                    <i class="fas fa-plus me-2"></i>Crear Primer Producto
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
