<div>
    <!-- Loading Overlay - Controlado por JS -->
    <div id="pdfLoadingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(0, 0, 0, 0.7); z-index: 9999; display: none !important;">
        <div class="text-center">
            <i class="fas fa-file-pdf text-light mb-3" style="font-size: 3rem; animation: pdfBlink 2s ease-in-out infinite;"></i>
            <h5 class="text-white">Generando PDF...</h5>
            <p class="text-white-50">Por favor espere mientras se procesa el documento</p>
        </div>
    </div>

    <style>
        @keyframes pdfBlink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.3;
            }
        }
    </style>

    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Reporte de Transacciones</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Filters -->
                    <div class="px-4 py-3 border-bottom">
                        <div class="row align-items-end">
                            <div class="col-12 col-md-3 mb-3 mb-md-0">
                                <label class="form-label">Prestamista</label>
                                <select wire:model="selectedBorrower" class="form-select">
                                    <option value="">Todos los prestamistas</option>
                                    @foreach($borrowers as $borrower)
                                        <option value="{{ $borrower->id }}">{{ $borrower->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-2 mb-3 mb-md-0">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" wire:model="dateFrom" class="form-control">
                            </div>
                            <div class="col-12 col-md-2 mb-3 mb-md-0">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" wire:model="dateTo" class="form-control">
                            </div>
                            <div class="col-12 col-md-5 text-md-end text-center">
                                <button wire:click="applyFilters" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <button wire:click="clearFilters" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="px-4 py-3 bg-light">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-sm mb-0">Total Transacciones</h6>
                                    <h4 class="font-weight-bolder">{{ $stats['total_transactions'] }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-sm mb-0">Monto Total</h6>
                                    <h4 class="font-weight-bolder text-primary">S/ {{ number_format($stats['total_amount'] / 100, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center">
                                    <h6 class="text-sm mb-0">Con Cuotas</h6>
                                    <h4 class="font-weight-bolder text-info">{{ $stats['with_installments'] }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div class="px-4 py-3">
                        <button onclick="showPdfOverlay()" wire:click="exportFullReportPdf" class="btn btn-success btn-sm" id="btnExportFullReport">
                            <i class="fas fa-file-pdf"></i> Exportar Reporte Completo PDF
                        </button>
                    </div>

                    <!-- Transactions Table -->
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="transactionsTable">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prestamista</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Descripción</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Monto</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cuotas</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</p>
                                    </td>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $transaction->lender->full_name ?? 'N/A' }}</h6>
                                                <p class="text-xs text-secondary mb-0">{{ $transaction->lender->email ?? '' }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs mb-0">{{ \Illuminate\Support\Str::limit($transaction->description, 50) }}</p>
                                        <p class="text-xs text-secondary mb-0">{{ $transaction->financialProduct->name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="text-xs font-weight-bold">S/ {{ number_format($transaction->amount / 100, 2) }}</span>
                                    </td>
                                    <td class="align-middle">
                                        @if($transaction->installment_progress['total'] > 0)
                                            <div class="px-2">
                                                <div class="d-flex align-items-center justify-content-center mb-1">
                                                    <span class="text-xs font-weight-bold">
                                                        {{ $transaction->installment_progress['paid'] }}/{{ $transaction->installment_progress['total'] }} pagadas
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    @php
                                                        $percentage = $transaction->installment_progress['percentage'];
                                                        $progressClass = 'bg-secondary';
                                                        if ($percentage >= 100) {
                                                            $progressClass = 'bg-success';
                                                        } elseif ($percentage > 0) {
                                                            $progressClass = 'bg-warning';
                                                        }
                                                    @endphp
                                                    <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $percentage }}%;" aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <div class="text-center mt-1">
                                                    <span class="text-xxs text-secondary">{{ $percentage }}%</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="text-center">
                                                <span class="text-xs text-secondary">Sin cuotas</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @php
                                            $status = $transaction->installment_progress['status'];
                                        @endphp
                                        @if($status === 'pagado')
                                            <span class="badge badge-sm bg-gradient-success">Pagado</span>
                                        @elseif($status === 'en_progreso')
                                            <span class="badge badge-sm bg-gradient-warning">En Progreso</span>
                                        @elseif($status === 'sin_cuotas')
                                            <span class="badge badge-sm bg-gradient-info">Sin Cuotas</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-danger">Pendiente</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($transaction->installment_progress['total'] > 0)
                                            <button type="button" wire:click.prevent="openInstallmentDetail({{ $transaction->id }})" class="btn btn-sm btn-primary mb-0 me-1" title="Ver Detalle de Cuotas">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        <button type="button" onclick="showPdfOverlay()" wire:click.prevent="exportTransactionPdf({{ $transaction->id }})" class="btn btn-sm btn-success mb-0" title="Exportar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <p class="text-xs text-secondary mb-0">No hay transacciones para mostrar</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Installment Detail Modal -->
    @if($showInstallmentModal)
    <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.7); z-index: 9999; position: fixed; top: 0; left: 0; width: 100%; height: 100%;" tabindex="-1">
        <div class="modal-dialog modal-xl" style="z-index: 10000; position: relative;">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Detalle de Cuotas - Transacción
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeInstallmentModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if($selectedTransaction)
                    <!-- Transaction Info -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-sm mb-2"><strong>Prestamista:</strong> {{ $selectedTransaction->lender->full_name ?? 'N/A' }}</h6>
                                    <p class="text-xs text-secondary mb-2">{{ $selectedTransaction->lender->email ?? '' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-sm mb-2"><strong>Tarjeta:</strong> {{ $selectedTransaction->financialProduct->name ?? 'N/A' }}</h6>
                                    <p class="text-xs text-secondary mb-2">{{ $selectedTransaction->financialProduct->institution ?? '' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-sm mb-2"><strong>Descripción:</strong> {{ $selectedTransaction->description }}</h6>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-sm mb-2"><strong>Monto Total:</strong> <span class="text-primary">S/ {{ number_format($selectedTransaction->amount / 100, 2) }}</span></h6>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-sm mb-2"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($selectedTransaction->transaction_date)->format('d/m/Y') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($installmentDetails)
                    <!-- Installment Summary -->
                    <div class="card mb-3 bg-light">
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <h6 class="text-xs text-secondary mb-1">Total Cuotas</h6>
                                    <h5 class="font-weight-bolder">{{ $installmentDetails->installment_count }}</h5>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-xs text-secondary mb-1">Monto por Cuota</h6>
                                    <h5 class="font-weight-bolder text-primary">S/ {{ number_format($installmentDetails->installment_amount / 100, 2) }}</h5>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-xs text-secondary mb-1">Total Pagado</h6>
                                    <h5 class="font-weight-bolder text-success">S/ {{ number_format($installmentDetails->total_paid / 100, 2) }}</h5>
                                </div>
                                <div class="col-md-3">
                                    <h6 class="text-xs text-secondary mb-1">Saldo Pendiente</h6>
                                    <h5 class="font-weight-bolder text-warning">S/ {{ number_format(($installmentDetails->total_amount - $installmentDetails->total_paid) / 100, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cuota by Cuota Schedule -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-gradient-dark">
                                <tr>
                                    <th class="text-white text-xs">Cuota</th>
                                    <th class="text-white text-xs">Fecha Programada</th>
                                    <th class="text-white text-xs">Monto Cuota</th>
                                    <th class="text-white text-xs">Monto Pagado</th>
                                    <th class="text-white text-xs">Fecha Pago</th>
                                    <th class="text-white text-xs">Saldo Restante</th>
                                    <th class="text-white text-xs text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($installmentSchedule as $cuota)
                                <tr class="{{ $cuota['status'] === 'paid' ? 'table-success' : ($cuota['status'] === 'overdue' ? 'table-danger' : ($cuota['status'] === 'partial' ? 'table-warning' : '')) }}">
                                    <td class="text-xs">
                                        <strong>{{ $cuota['number'] }}/{{ $cuota['total'] }}</strong>
                                    </td>
                                    <td class="text-xs">
                                        {{ \Carbon\Carbon::parse($cuota['scheduled_date'])->format('d/m/Y') }}
                                    </td>
                                    <td class="text-xs">
                                        S/ {{ number_format($cuota['installment_amount'] / 100, 2) }}
                                    </td>
                                    <td class="text-xs {{ $cuota['paid_amount'] > 0 ? 'text-success font-weight-bold' : '' }}">
                                        S/ {{ number_format($cuota['paid_amount'] / 100, 2) }}
                                    </td>
                                    <td class="text-xs">
                                        {{ $cuota['payment_date'] ? \Carbon\Carbon::parse($cuota['payment_date'])->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-xs">
                                        S/ {{ number_format($cuota['remaining_balance'] / 100, 2) }}
                                    </td>
                                    <td class="text-xs text-center">
                                        @if($cuota['status'] === 'paid')
                                            <span class="badge badge-sm bg-gradient-success">Pagado</span>
                                        @elseif($cuota['status'] === 'partial')
                                            <span class="badge badge-sm bg-gradient-warning">Parcial</span>
                                        @elseif($cuota['status'] === 'overdue')
                                            <span class="badge badge-sm bg-gradient-danger">Vencido</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-secondary">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No se encontró plan de cuotas asociado a esta transacción
                    </div>
                    @endif
                    @endif
                </div>
                <div class="modal-footer">
                    @if($selectedTransaction && $installmentDetails)
                    <button type="button" onclick="showPdfOverlay()" wire:click.prevent="exportTransactionPdf({{ $selectedTransaction->id }})" class="btn btn-success btn-sm">
                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                    </button>
                    @endif
                    <button type="button" class="btn btn-secondary btn-sm" wire:click.prevent="closeInstallmentModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        // Función para mostrar el overlay de carga
        function showPdfOverlay() {
            const overlay = document.getElementById('pdfLoadingOverlay');
            if (overlay) {
                overlay.style.setProperty('display', 'flex', 'important');
            }
        }

        // Función para ocultar el overlay de carga
        function hidePdfOverlay() {
            const overlay = document.getElementById('pdfLoadingOverlay');
            if (overlay) {
                overlay.style.setProperty('display', 'none', 'important');
            }
        }

        document.addEventListener('livewire:init', () => {
            Livewire.on('filtersApplied', () => {
                console.log('Filters applied');
            });

            // Ocultar overlay después de que se complete la descarga
            // El overlay se ocultará después de 3 segundos de mostrado
            let overlayTimer;
            window.addEventListener('click', (e) => {
                if (e.target.closest('button[onclick*="showPdfOverlay"]')) {
                    clearTimeout(overlayTimer);
                    overlayTimer = setTimeout(() => {
                        hidePdfOverlay();
                    }, 3000);
                }
            });
        });
    </script>
    @endpush
</div>
