<div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Productos Financieros</h6>
                        <a href="{{ route('products.create') }}" class="btn btn-sm bg-gradient-success mb-0" wire:navigate>
                            <i class="fas fa-plus me-2"></i>Nuevo Producto
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($products->count() > 0)
                        <div class="table-responsive p-3" wire:ignore.self>
                            <table id="productsTable" class="table table-hover align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tipo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Límite/Préstamo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Saldo Actual</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Disponible</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    @php
                                                        $icons = [
                                                            'credit_card' => 'credit-card',
                                                            'debit_card' => 'credit-card',
                                                            'digital_wallet' => 'wallet',
                                                            'credit_line' => 'chart-line',
                                                            'savings_account' => 'piggy-bank',
                                                            'personal_loan' => 'hand-holding-usd',
                                                            'asset_loan' => 'car'
                                                        ];
                                                        $icon = $icons[$product->product_type] ?? 'money-bill';
                                                    @endphp
                                                    <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-{{ $icon }} text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $product->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $product->institution ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $types = [
                                                        'credit_card' => 'Tarjeta de Crédito',
                                                        'debit_card' => 'Tarjeta de Débito',
                                                        'digital_wallet' => 'Billetera Digital',
                                                        'credit_line' => 'Línea de Crédito',
                                                        'savings_account' => 'Cuenta de Ahorros',
                                                        'personal_loan' => 'Préstamo Personal',
                                                        'asset_loan' => 'Crédito por Bien'
                                                    ];
                                                @endphp
                                                <p class="text-xs font-weight-bold mb-0">{{ $types[$product->product_type] ?? 'N/A' }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($product->credit_limit > 0)
                                                    <span class="text-xs font-weight-bold">S/ {{ number_format($product->credit_limit / 100, 2) }}</span>
                                                @elseif($product->loan_amount > 0)
                                                    <span class="text-xs font-weight-bold">S/ {{ number_format($product->loan_amount / 100, 2) }}</span>
                                                @else
                                                    <span class="text-xs text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">S/ {{ number_format($product->current_balance / 100, 2) }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($product->credit_limit > 0)
                                                    @php
                                                        $available = $product->credit_limit - $product->current_balance;
                                                        $percentage = ($available / $product->credit_limit) * 100;
                                                        $colorClass = $percentage > 50 ? 'success' : ($percentage > 25 ? 'warning' : 'danger');
                                                    @endphp
                                                    <span class="text-xs font-weight-bold text-{{ $colorClass }}">
                                                        S/ {{ number_format($available / 100, 2) }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <button wire:click="toggleStatus({{ $product->id }})"
                                                        class="badge badge-sm bg-gradient-{{ $product->is_active ? 'success' : 'secondary' }} border-0"
                                                        style="cursor: pointer;">
                                                    {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                                </button>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    @if($product->isLoan())
                                                        <a href="{{ route('products.loan-schedule', $product) }}"
                                                           class="btn btn-sm btn-outline-info mb-0"
                                                           wire:navigate
                                                           title="Cronograma">
                                                            <i class="fas fa-calendar-alt"></i>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('products.edit', $product) }}"
                                                       class="btn btn-sm btn-outline-primary mb-0"
                                                       wire:navigate
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button wire:click="deleteProduct({{ $product->id }})"
                                                            wire:confirm="¿Está seguro de eliminar este producto?"
                                                            class="btn btn-sm btn-outline-danger mb-0"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-3">No hay productos financieros registrados</p>
                            <a href="{{ route('products.create') }}" class="btn bg-gradient-success" wire:navigate>
                                <i class="fas fa-plus me-2"></i>Crear Primer Producto
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($products->count() > 0)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initProductsTable();
            });

            document.addEventListener('livewire:navigated', function() {
                initProductsTable();
            });

            function initProductsTable() {
                if ($.fn.DataTable.isDataTable('#productsTable')) {
                    $('#productsTable').DataTable().destroy();
                }

                $('#productsTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 25,
                    order: [[0, 'asc']], // Ordenar por nombre
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-2"></i>Excel',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-2"></i>Imprimir',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        }
                    ],
                    columnDefs: [
                        { orderable: false, targets: [6] }
                    ]
                });
            }
        </script>
        @endpush
    @endif
</div>
