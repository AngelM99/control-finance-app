<div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Transacciones</h6>
                        @can('create transactions')
                            <a href="{{ route('transactions.create') }}" class="btn btn-sm bg-gradient-success mb-0" wire:navigate>
                                <i class="fas fa-plus me-2"></i>Nueva Transacción
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($transactions->count() > 0)
                        <div class="table-responsive p-3" wire:ignore.self>
                            <table id="transactionsTable" class="table table-hover align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Producto</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Usuario/Prestamista</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Descripción</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Monto</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0 ps-3">
                                                    {{ $transaction->transaction_date->format('d/m/Y') }}
                                                </p>
                                                <p class="text-xs text-secondary mb-0 ps-3">
                                                    {{ $transaction->transaction_date->format('H:i') }}
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ $transaction->financialProduct?->name ?? 'N/A' }}
                                                </p>
                                            </td>
                                            <td>
                                                @if($transaction->lender)
                                                    <span class="badge badge-sm bg-gradient-warning">
                                                        <i class="fas fa-user me-1"></i>{{ $transaction->lender->full_name }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">
                                                        <i class="fas fa-user-circle me-1"></i>Propietario
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <p class="text-xs mb-0">{{ $transaction->description ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @php
                                                    $typeColors = [
                                                        'purchase' => 'info',
                                                        'payment' => 'success',
                                                        'transfer' => 'warning',
                                                        'withdrawal' => 'danger',
                                                        'deposit' => 'primary',
                                                        'refund' => 'secondary',
                                                        'adjustment' => 'dark'
                                                    ];
                                                    $color = $typeColors[$transaction->transaction_type] ?? 'info';
                                                @endphp
                                                <span class="badge badge-sm bg-gradient-{{ $color }}">
                                                    {{ ucfirst($transaction->transaction_type) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">
                                                    S/ {{ number_format($transaction->amount / 100, 2) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    @can('edit transactions')
                                                        <a href="{{ route('transactions.edit', $transaction) }}"
                                                           class="btn btn-sm btn-outline-primary mb-0"
                                                           wire:navigate
                                                           title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endcan
                                                    @can('delete transactions')
                                                        <button wire:click="delete({{ $transaction->id }})"
                                                                wire:confirm="¿Está seguro de eliminar esta transacción?"
                                                                class="btn btn-sm btn-outline-danger mb-0"
                                                                title="Eliminar">
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
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-3">No hay transacciones registradas</p>
                            @can('create transactions')
                                <a href="{{ route('transactions.create') }}" class="btn bg-gradient-success" wire:navigate>
                                    <i class="fas fa-plus me-2"></i>Registrar Primera Transacción
                                </a>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($transactions->count() > 0)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initDataTable();
            });

            // Reinicializar después de navegación Livewire
            document.addEventListener('livewire:navigated', function() {
                initDataTable();
            });

            function initDataTable() {
                // Destruir instancia previa si existe
                if ($.fn.DataTable.isDataTable('#transactionsTable')) {
                    $('#transactionsTable').DataTable().destroy();
                }

                // Inicializar DataTable
                $('#transactionsTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 25,
                    order: [[0, 'desc']], // Ordenar por fecha descendente
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-2"></i>Excel',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5] // Excluir columna de acciones
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
                        { orderable: false, targets: [6] } // Deshabilitar ordenamiento en columna de acciones
                    ]
                });
            }
        </script>
        @endpush
    @endif
</div>
