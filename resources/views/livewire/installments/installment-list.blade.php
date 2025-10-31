<div>
    <div class="row">
        <div class="col-12">
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-hourglass-half text-info fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Activos</h6>
                            <p class="text-sm mb-0 font-weight-bold">{{ $summary['active_count'] }}</p>
                            <p class="text-xs text-secondary mb-0">${{ number_format($summary['active_amount'] / 100, 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-check-circle text-success fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Completados</h6>
                            <p class="text-sm mb-0 font-weight-bold">{{ $summary['completed_count'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-dollar-sign text-primary fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Total</h6>
                            <p class="text-sm mb-0 font-weight-bold">${{ number_format($summary['total_amount'] / 100, 2) }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                        <i class="fas fa-shopping-cart text-warning fa-2x me-3"></i>
                        <div>
                            <h6 class="mb-0">Planes</h6>
                            <p class="text-sm mb-0 font-weight-bold">{{ $installments->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Cuotas / Pagos Programados</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($installments->count() > 0)
                        <div class="table-responsive p-3" wire:ignore.self>
                            <table id="installmentsTable" class="table table-hover align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Producto</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Descripción / Compra</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Progreso</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Monto por Cuota</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Primer Pago</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($installments as $installment)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="icon icon-shape icon-sm bg-gradient-info shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-calendar-alt text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $installment->financialProduct?->name ?? 'N/A' }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $installment->merchant ?? '' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs mb-0">{{ $installment->description ?? 'Compra en cuotas' }}</p>
                                                <p class="text-xxs text-secondary mb-0">{{ $installment->purchase_date?->format('d/m/Y') ?? '' }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-xs font-weight-bold">
                                                    {{ $installment->current_installment }} / {{ $installment->installment_count }}
                                                </span>
                                                <div class="progress mt-1" style="height: 4px;">
                                                    <div class="progress-bar bg-gradient-info" role="progressbar"
                                                         style="width: {{ $installment->progress_percentage }}%"
                                                         aria-valuenow="{{ $installment->progress_percentage }}"
                                                         aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">${{ number_format($installment->installment_amount / 100, 2) }}</span>
                                                <p class="text-xxs text-secondary mb-0">Total: ${{ number_format($installment->total_amount / 100, 2) }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ $installment->first_payment_date?->format('d/m/Y') ?? 'N/A' }}
                                                </p>
                                                <p class="text-xxs text-secondary mb-0">Primer pago</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if($installment->status === 'completed')
                                                    <span class="badge badge-sm bg-gradient-success">Completado</span>
                                                @elseif($installment->status === 'canceled')
                                                    <span class="badge badge-sm bg-gradient-danger">Cancelado</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-info">Activo</span>
                                                    <p class="text-xxs text-secondary mb-0">{{ $installment->remaining_installments }} restantes</p>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    @if($installment->status === 'active')
                                                        <button wire:click="markAsPaid({{ $installment->id }})"
                                                                wire:confirm="¿Marcar una cuota como pagada?"
                                                                class="btn btn-sm bg-gradient-success mb-0"
                                                                title="Pagar cuota">
                                                            <i class="fas fa-check me-1"></i>Pagar
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-0">No hay cuotas registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($installments->count() > 0)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initInstallmentsTable();
            });

            document.addEventListener('livewire:navigated', function() {
                initInstallmentsTable();
            });

            function initInstallmentsTable() {
                if ($.fn.DataTable.isDataTable('#installmentsTable')) {
                    $('#installmentsTable').DataTable().destroy();
                }

                $('#installmentsTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 25,
                    order: [[4, 'desc']], // Ordenar por fecha de primer pago descendente
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
