<div>
    <!-- Loading Overlay -->
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
                    <h6>Estado de Cuenta por Prestamista</h6>
                    <p class="text-sm text-secondary mb-0">Consulta el estado de cuenta mensual de un prestamista específico</p>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Filters Section -->
                    <div class="px-4 py-3 border-bottom bg-gradient-light">
                        <form wire:submit.prevent="generateReport">
                            <div class="row align-items-end">
                                <div class="col-12 col-md-4 mb-3 mb-md-0">
                                    <label class="form-label text-sm font-weight-bold">
                                        Prestamista <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="selectedLender" class="form-select @error('selectedLender') is-invalid @enderror" required>
                                        <option value="">Seleccione un prestamista</option>
                                        @foreach($lenders as $lender)
                                            <option value="{{ $lender->id }}">{{ $lender->full_name }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedLender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-3 mb-3 mb-md-0">
                                    <label class="form-label text-sm font-weight-bold">
                                        Mes <span class="text-danger">*</span>
                                    </label>
                                    <select wire:model="selectedMonth" class="form-select @error('selectedMonth') is-invalid @enderror" required>
                                        @foreach($months as $key => $month)
                                            <option value="{{ $key }}">{{ $month }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedMonth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-2 mb-3 mb-md-0">
                                    <label class="form-label text-sm font-weight-bold">
                                        Año <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="number"
                                        wire:model="selectedYear"
                                        class="form-control @error('selectedYear') is-invalid @enderror"
                                        min="2020"
                                        max="2030"
                                        step="1"
                                        placeholder="Ej: 2025"
                                        required
                                    >
                                    @error('selectedYear')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-3 text-md-end text-center">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="fas fa-search"></i> Generar Reporte
                                    </button>
                                    <button type="button" wire:click="clearSearch" class="btn btn-secondary btn-sm">
                                        <i class="fas fa-times"></i> Limpiar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    @if($searchExecuted && !empty($reportData))
                        <!-- Report Header -->
                        <div class="px-4 py-3 bg-light border-bottom">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="text-dark font-weight-bold mb-1">{{ $reportData['lender']->full_name }}</h5>
                                    <p class="text-sm text-secondary mb-0">
                                        <i class="fas fa-envelope me-1"></i> {{ $reportData['lender']->email }}
                                    </p>
                                    @if($reportData['lender']->phone)
                                        <p class="text-sm text-secondary mb-0">
                                            <i class="fas fa-phone me-1"></i> {{ $reportData['lender']->phone }}
                                        </p>
                                    @endif
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <h5 class="text-dark font-weight-bold mb-1">Período</h5>
                                    <p class="text-sm text-secondary mb-0">
                                        {{ ucfirst($reportData['period']['month_name']) }} {{ $reportData['period']['year'] }}
                                    </p>
                                    <p class="text-xs text-secondary mb-0">
                                        {{ $reportData['period']['start_date']->format('d/m/Y') }} - {{ $reportData['period']['end_date']->format('d/m/Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Cards -->
                        <div class="px-4 py-3 bg-gradient-primary">
                            <div class="row">
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="card">
                                        <div class="card-body p-3 text-center">
                                            <p class="text-xs text-secondary mb-1">Total Cuotas del Mes</p>
                                            <h5 class="font-weight-bolder text-primary mb-0">
                                                S/ {{ number_format($reportData['summary']['total_due_amount'] / 100, 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="card">
                                        <div class="card-body p-3 text-center">
                                            <p class="text-xs text-secondary mb-1">Cuotas Pagadas</p>
                                            <h5 class="font-weight-bolder text-success mb-0">
                                                S/ {{ number_format($reportData['summary']['total_due_paid'] / 100, 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body p-3 text-center">
                                            <p class="text-xs text-secondary mb-1">Cuotas Pendientes</p>
                                            <h5 class="font-weight-bolder text-danger mb-0">
                                                S/ {{ number_format($reportData['summary']['total_due_pending'] / 100, 2) }}
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Export Button -->
                        <div class="px-4 py-3 border-bottom">
                            <button onclick="showPdfOverlay()" wire:click="exportPdf" class="btn btn-success btn-sm">
                                <i class="fas fa-file-pdf"></i> Exportar PDF
                            </button>
                        </div>

                        <!-- Installments Due Section -->
                        @if(count($reportData['installments_due']) > 0)
                        <div class="px-4 py-3 border-top">
                            <h6 class="text-sm font-weight-bold mb-3">
                                <i class="fas fa-calendar-check me-2"></i>Cuotas con Vencimiento en el Período
                            </h6>
                            @foreach($reportData['installments_due'] as $item)
                            <div class="card mb-3">
                                <div class="card-header pb-0">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="text-sm mb-1 font-weight-bold">{{ $item['installment']->description }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $item['installment']->financialProduct->name }}</p>
                                        </div>
                                        <div class="text-end">
                                            <p class="text-xs text-secondary mb-0">Total a pagar en período:</p>
                                            <h6 class="text-warning mb-0">S/ {{ number_format($item['total_due'] / 100, 2) }}</h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body pt-2">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="text-xs">Cuota</th>
                                                    <th class="text-xs">Vencimiento</th>
                                                    <th class="text-xs text-end">Monto</th>
                                                    <th class="text-xs text-center">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($item['due_installments'] as $due)
                                                <tr class="{{ $due['is_paid'] ? 'table-success' : ($due['status'] === 'overdue' ? 'table-danger' : '') }}">
                                                    <td class="text-xs">{{ $due['number'] }}/{{ $due['total'] }}</td>
                                                    <td class="text-xs">{{ $due['due_date']->format('d/m/Y') }}</td>
                                                    <td class="text-xs text-end">S/ {{ number_format($due['installment_amount'] / 100, 2) }}</td>
                                                    <td class="text-xs text-center">
                                                        @if($due['is_paid'])
                                                            <span class="badge badge-sm bg-success">Pagado</span>
                                                        @elseif($due['status'] === 'overdue')
                                                            <span class="badge badge-sm bg-danger">Vencido</span>
                                                        @else
                                                            <span class="badge badge-sm bg-warning">Pendiente</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- No data messages -->
                        @if(count($reportData['installments_due']) === 0)
                        <div class="px-4 py-5 text-center">
                            <i class="fas fa-check-circle text-success mb-3" style="font-size: 3rem;"></i>
                            <h6 class="text-success">No hay cuotas a pagar en este período</h6>
                            <p class="text-xs text-secondary mb-0">El prestamista no tiene cuotas con vencimiento en {{ ucfirst($reportData['period']['month_name']) }} {{ $reportData['period']['year'] }}</p>
                        </div>
                        @endif
                    @elseif($searchExecuted)
                        <div class="px-4 py-5 text-center">
                            <i class="fas fa-exclamation-triangle text-warning mb-3" style="font-size: 3rem;"></i>
                            <h6 class="text-secondary">No se encontraron datos</h6>
                            <p class="text-xs text-secondary mb-0">Complete los filtros obligatorios y haga clic en "Generar Reporte"</p>
                        </div>
                    @else
                        <div class="px-4 py-5 text-center">
                            <i class="fas fa-search text-secondary mb-3" style="font-size: 3rem;"></i>
                            <h6 class="text-secondary">Genere un reporte</h6>
                            <p class="text-xs text-secondary mb-0">Seleccione un prestamista y período, luego haga clic en "Generar Reporte"</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showPdfOverlay() {
            const overlay = document.getElementById('pdfLoadingOverlay');
            if (overlay) {
                overlay.style.setProperty('display', 'flex', 'important');
            }
        }

        function hidePdfOverlay() {
            const overlay = document.getElementById('pdfLoadingOverlay');
            if (overlay) {
                overlay.style.setProperty('display', 'none', 'important');
            }
        }

        document.addEventListener('livewire:init', () => {
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
