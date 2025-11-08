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
                            <p class="text-xs text-secondary mb-0">S/ {{ number_format($summary['active_amount'] / 100, 2) }}</p>
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
                            <p class="text-sm mb-0 font-weight-bold">S/ {{ number_format($summary['total_amount'] / 100, 2) }}</p>
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
                                                <span class="text-xs font-weight-bold">S/ {{ number_format($installment->installment_amount / 100, 2) }}</span>
                                                <p class="text-xxs text-secondary mb-0">Total: S/ {{ number_format($installment->total_amount / 100, 2) }}</p>
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
                                                        <button wire:click="openPaymentModal({{ $installment->id }})"
                                                                class="btn btn-sm bg-gradient-success mb-0"
                                                                title="Registrar pago">
                                                            <i class="fas fa-dollar-sign me-1"></i>Pagar
                                                        </button>
                                                    @endif
                                                    @if($installment->status === 'completed')
                                                        <span class="text-xs text-success">
                                                            <i class="fas fa-check-circle"></i> Pagado
                                                        </span>
                                                    @endif
                                                    @if(($installment->payment_schedule['payments'] ?? []) && count($installment->payment_schedule['payments'] ?? []) > 0)
                                                        <button wire:click="openHistoryModal({{ $installment->id }})"
                                                                class="btn btn-sm btn-outline-info mb-0"
                                                                title="Ver historial de pagos">
                                                            <i class="fas fa-history"></i>
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

    <!-- Payment Modal -->
    @if($showPaymentModal && $selectedInstallment)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7); z-index: 9999; position: fixed; top: 0; left: 0; width: 100%; height: 100%;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" style="z-index: 10000;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-dollar-sign me-2"></i>Registrar Pago
                    </h5>
                    <button type="button" class="btn-close" wire:click="closePaymentModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Installment Details -->
                    <div class="card card-body border mb-3">
                        <h6 class="text-sm font-weight-bold mb-2">{{ $selectedInstallment->financialProduct?->name ?? 'N/A' }}</h6>
                        <p class="text-xs text-secondary mb-1">{{ $selectedInstallment->description ?? 'Compra en cuotas' }}</p>
                        <hr class="horizontal dark my-2">
                        <div class="row">
                            <div class="col-6">
                                <p class="text-xs mb-1">Total:</p>
                                <p class="text-sm font-weight-bold mb-0">S/ {{ number_format($selectedInstallment->total_amount / 100, 2) }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-xs mb-1">Pagado:</p>
                                <p class="text-sm font-weight-bold text-success mb-0">S/ {{ number_format(($selectedInstallment->total_paid ?? 0) / 100, 2) }}</p>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <p class="text-xs mb-1">Restante:</p>
                                <p class="text-sm font-weight-bold text-warning mb-0">S/ {{ number_format($selectedInstallment->remaining_amount / 100, 2) }}</p>
                            </div>
                            <div class="col-6">
                                <p class="text-xs mb-1">Cuotas:</p>
                                <p class="text-sm font-weight-bold mb-0">{{ $selectedInstallment->current_installment }} / {{ $selectedInstallment->installment_count }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Form -->
                    <form wire:submit.prevent="processPayment">
                        <div class="mb-3">
                            <label for="paymentDate" class="form-label text-sm">Fecha de Pago</label>
                            <input type="date"
                                   class="form-control @error('paymentDate') is-invalid @enderror"
                                   id="paymentDate"
                                   wire:model.defer="paymentDate"
                                   max="{{ date('Y-m-d') }}">
                            @error('paymentDate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="paymentAmount" class="form-label text-sm">
                                Monto a Pagar
                                <span class="text-xs text-secondary">(Sugerido: S/ {{ number_format($suggestedAmount / 100, 2) }})</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number"
                                       class="form-control @error('paymentAmount') is-invalid @enderror"
                                       id="paymentAmount"
                                       wire:model.defer="paymentAmount"
                                       step="0.01"
                                       min="0.01"
                                       max="{{ number_format($selectedInstallment->remaining_amount / 100, 2, '.', '') }}"
                                       placeholder="0.00">
                                @error('paymentAmount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-xs text-secondary">
                                Puede pagar el monto completo, una cuota, o un pago parcial.
                            </small>
                        </div>

                        <div class="alert alert-info text-xs py-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            El monto sugerido corresponde a
                            @if($selectedInstallment->remaining_amount <= $selectedInstallment->installment_amount)
                                el saldo total restante.
                            @else
                                @php
                                    $totalPaid = $selectedInstallment->total_paid ?? 0;
                                    $partialAmount = $totalPaid % $selectedInstallment->installment_amount;
                                @endphp
                                @if($partialAmount > 0)
                                    completar la cuota actual.
                                @else
                                    una cuota completa.
                                @endif
                            @endif
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closePaymentModal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="button" class="btn bg-gradient-success btn-sm" wire:click="processPayment">
                        <i class="fas fa-check me-1"></i>Registrar Pago
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- History Modal -->
    @if($showHistoryModal && $selectedInstallment)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7); z-index: 9999; position: fixed; top: 0; left: 0; width: 100%; height: 100%;" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="z-index: 10000;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-history me-2"></i>Historial de Pagos
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeHistoryModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Installment Details -->
                    <div class="card card-body border mb-3">
                        <h6 class="text-sm font-weight-bold mb-2">{{ $selectedInstallment->financialProduct?->name ?? 'N/A' }}</h6>
                        <p class="text-xs text-secondary mb-1">{{ $selectedInstallment->description ?? 'Compra en cuotas' }}</p>
                        <hr class="horizontal dark my-2">
                        <div class="row">
                            <div class="col-4">
                                <p class="text-xs mb-1">Total:</p>
                                <p class="text-sm font-weight-bold mb-0">S/ {{ number_format($selectedInstallment->total_amount / 100, 2) }}</p>
                            </div>
                            <div class="col-4">
                                <p class="text-xs mb-1">Pagado:</p>
                                <p class="text-sm font-weight-bold text-success mb-0">S/ {{ number_format(($selectedInstallment->total_paid ?? 0) / 100, 2) }}</p>
                            </div>
                            <div class="col-4">
                                <p class="text-xs mb-1">Restante:</p>
                                <p class="text-sm font-weight-bold text-warning mb-0">S/ {{ number_format($selectedInstallment->remaining_amount / 100, 2) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment History -->
                    @php
                        $payments = $selectedInstallment->payment_schedule['payments'] ?? [];
                    @endphp

                    @if(count($payments) > 0)
                        <h6 class="text-sm font-weight-bold mb-3">Pagos Registrados ({{ count($payments) }})</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-items-center mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-xs">#</th>
                                        <th class="text-xs">Fecha de Pago</th>
                                        <th class="text-xs text-end">Monto Pagado</th>
                                        <th class="text-xs text-end">Saldo Después</th>
                                        <th class="text-xs">Registrado</th>
                                        <th class="text-xs text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payments as $index => $payment)
                                    <tr>
                                        <td class="text-xs">{{ $index + 1 }}</td>
                                        <td class="text-xs">
                                            <i class="fas fa-calendar-check text-success me-1"></i>
                                            {{ \Carbon\Carbon::parse($payment['payment_date'])->format('d/m/Y') }}
                                        </td>
                                        <td class="text-xs text-end font-weight-bold text-success">
                                            S/ {{ number_format($payment['amount_dollars'], 2) }}
                                        </td>
                                        <td class="text-xs text-end">
                                            S/ {{ number_format($payment['remaining_after_dollars'], 2) }}
                                        </td>
                                        <td class="text-xs text-muted">
                                            {{ \Carbon\Carbon::parse($payment['registered_at'])->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="text-xs text-center">
                                            <button wire:click="deletePayment({{ $index }})"
                                                    wire:confirm="¿Está seguro de eliminar este pago de S/ {{ number_format($payment['amount_dollars'], 2) }}? Esta acción actualizará el saldo de la cuota."
                                                    class="btn btn-sm btn-outline-danger mb-0"
                                                    title="Eliminar pago">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-warning text-xs mt-3 mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Importante:</strong> Al eliminar un pago, se actualizará automáticamente el saldo de la cuota y del producto financiero.
                        </div>
                    @else
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            No hay pagos registrados para esta cuota.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="closeHistoryModal">
                        <i class="fas fa-times me-1"></i>Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
