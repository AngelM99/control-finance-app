<div>
    <div class="row">
        <div class="col-12">
            <!-- Global Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-users text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Prestamistas</h6>
                            <p class="text-sm mb-0 font-weight-bold">{{ $globalStats['total_borrowers'] }}</p>
                            <p class="text-xs text-secondary mb-0">Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-hand-holding-usd text-info fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Total Prestado</h6>
                            <p class="text-sm mb-0 font-weight-bold">S/ {{ number_format($globalStats['total_lent'] / 100, 2) }}</p>
                            <p class="text-xs text-secondary mb-0">Monto total</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Total Pagado</h6>
                            <p class="text-sm mb-0 font-weight-bold text-success">S/ {{ number_format($globalStats['total_paid'] / 100, 2) }}</p>
                            <p class="text-xs text-secondary mb-0">Recuperado</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-exclamation-triangle text-warning fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Deuda Pendiente</h6>
                            <p class="text-sm mb-0 font-weight-bold text-warning">S/ {{ number_format($globalStats['total_debt'] / 100, 2) }}</p>
                            <p class="text-xs text-secondary mb-0">Por cobrar</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Borrowers Table -->
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Reporte de Prestamistas</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($borrowers->count() > 0)
                        <div class="table-responsive p-3">
                            <table id="borrowersTable" class="table table-hover align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prestamista</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Prestado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total Pagado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Saldo Pendiente</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cuotas Activas</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cuotas Completadas</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($borrowers as $borrower)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $borrower['name'] }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $borrower['email'] }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">S/ {{ number_format($borrower['total_lent'] / 100, 2) }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold text-success">S/ {{ number_format($borrower['total_paid'] / 100, 2) }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold text-warning">S/ {{ number_format($borrower['remaining_debt'] / 100, 2) }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm bg-gradient-info">{{ $borrower['active_installments_count'] }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm bg-gradient-success">{{ $borrower['completed_installments_count'] }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if($borrower['status'] === 'active')
                                                    <span class="badge badge-sm bg-gradient-warning">Activo</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-success">Completado</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <button wire:click="openDetailModal({{ $borrower['id'] }})"
                                                        class="btn btn-sm bg-gradient-primary mb-0"
                                                        title="Ver detalle">
                                                    <i class="fas fa-eye me-1"></i>Ver Detalle
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-0">No hay prestamistas registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($borrowers->count() > 0)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initBorrowersTable();
            });

            document.addEventListener('livewire:navigated', function() {
                initBorrowersTable();
            });

            function initBorrowersTable() {
                if ($.fn.DataTable.isDataTable('#borrowersTable')) {
                    $('#borrowersTable').DataTable().destroy();
                }

                $('#borrowersTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 25,
                    order: [[3, 'desc']], // Ordenar por saldo pendiente descendente
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-2"></i>Excel',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-2"></i>Imprimir',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            }
                        }
                    ],
                    columnDefs: [
                        { orderable: false, targets: [7] }
                    ]
                });
            }
        </script>
        @endpush
    @endif

    <!-- Detail Modal -->
    @if($showDetailModal && $selectedBorrower)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7); z-index: 9999; position: fixed; top: 0; left: 0; width: 100%; height: 100%; overflow-y: auto;" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="z-index: 10000; max-width: 90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-user-circle me-2"></i>Detalle de Prestamista: {{ $selectedBorrower->name }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeDetailModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Borrower Info -->
                    <div class="card card-body border mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-sm mb-1"><strong>Nombre:</strong> {{ $selectedBorrower->name }}</p>
                                <p class="text-sm mb-1"><strong>Email:</strong> {{ $selectedBorrower->email }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="text-sm mb-1"><strong>Teléfono:</strong> {{ $selectedBorrower->phone ?? 'N/A' }}</p>
                                <p class="text-sm mb-1"><strong>Registro:</strong> {{ $selectedBorrower->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Summary -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card card-body border text-center">
                                <p class="text-xs text-secondary mb-1">Total Prestado</p>
                                <p class="text-lg font-weight-bold mb-0">S/ {{ number_format($borrowerStats['total_lent'] / 100, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-body border text-center">
                                <p class="text-xs text-secondary mb-1">Total Pagado</p>
                                <p class="text-lg font-weight-bold text-success mb-0">S/ {{ number_format($borrowerStats['total_paid'] / 100, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-body border text-center">
                                <p class="text-xs text-secondary mb-1">Saldo Pendiente</p>
                                <p class="text-lg font-weight-bold text-warning mb-0">S/ {{ number_format($borrowerStats['remaining_debt'] / 100, 2) }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-body border text-center">
                                <p class="text-xs text-secondary mb-1">Progreso</p>
                                <p class="text-lg font-weight-bold mb-0">{{ number_format($borrowerStats['progress_percentage'], 1) }}%</p>
                                <div class="progress mt-1" style="height: 6px;">
                                    <div class="progress-bar bg-gradient-success" role="progressbar"
                                         style="width: {{ $borrowerStats['progress_percentage'] }}%"
                                         aria-valuenow="{{ $borrowerStats['progress_percentage'] }}"
                                         aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions History -->
                    <div class="card mb-3">
                        <div class="card-header pb-0">
                            <h6>Historial de Transacciones</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            @if($borrowerTransactions->count() > 0)
                                <div class="table-responsive p-3">
                                    <table class="table table-sm align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Descripción</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Monto</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cuotas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($borrowerTransactions as $transaction)
                                                <tr>
                                                    <td><p class="text-xs mb-0 ps-3">{{ $transaction->transaction_date->format('d/m/Y') }}</p></td>
                                                    <td><p class="text-xs mb-0">{{ $transaction->financialProduct?->name ?? 'N/A' }}</p></td>
                                                    <td><span class="badge badge-sm bg-gradient-info">{{ ucfirst($transaction->transaction_type) }}</span></td>
                                                    <td><p class="text-xs mb-0">{{ $transaction->description ?? '-' }}</p></td>
                                                    <td class="align-middle text-center"><span class="text-xs font-weight-bold">S/ {{ number_format($transaction->amount / 100, 2) }}</span></td>
                                                    <td class="align-middle text-center"><span class="text-xs">{{ $transaction->installment_count ?? '-' }}</span></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-sm text-secondary mb-0">No hay transacciones</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Installments Plans -->
                    <div class="card mb-3">
                        <div class="card-header pb-0">
                            <h6>Planes de Cuotas</h6>
                        </div>
                        <div class="card-body px-0 pt-0 pb-2">
                            @if($borrowerInstallments->count() > 0)
                                <div class="table-responsive p-3">
                                    <table class="table table-sm align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progreso</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pagado</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Restante</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($borrowerInstallments as $installment)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex flex-column ps-3">
                                                            <p class="text-xs font-weight-bold mb-0">{{ $installment->financialProduct?->name ?? 'N/A' }}</p>
                                                            <p class="text-xxs text-secondary mb-0">{{ $installment->description ?? '' }}</p>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center">
                                                        <span class="text-xs font-weight-bold">{{ $installment->current_installment }} / {{ $installment->installment_count }}</span>
                                                        <div class="progress mt-1" style="height: 4px;">
                                                            <div class="progress-bar bg-gradient-info" role="progressbar"
                                                                 style="width: {{ $installment->progress_percentage }}%"
                                                                 aria-valuenow="{{ $installment->progress_percentage }}"
                                                                 aria-valuemin="0" aria-valuemax="100"></div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center"><span class="text-xs">S/ {{ number_format($installment->total_amount / 100, 2) }}</span></td>
                                                    <td class="align-middle text-center"><span class="text-xs text-success">S/ {{ number_format(($installment->total_paid ?? 0) / 100, 2) }}</span></td>
                                                    <td class="align-middle text-center"><span class="text-xs text-warning">S/ {{ number_format($installment->remaining_amount / 100, 2) }}</span></td>
                                                    <td class="align-middle text-center">
                                                        @if($installment->status === 'completed')
                                                            <span class="badge badge-sm bg-gradient-success">Completado</span>
                                                        @elseif($installment->status === 'canceled')
                                                            <span class="badge badge-sm bg-gradient-danger">Cancelado</span>
                                                        @else
                                                            <span class="badge badge-sm bg-gradient-info">Activo</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <p class="text-sm text-secondary mb-0">No hay planes de cuotas</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeDetailModal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
