<div>
    <div class="container-fluid py-4">
        <!-- Resumen del Préstamo -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-0">{{ $product->name }}</h6>
                                <p class="text-sm text-secondary mb-0">
                                    {{ $product->institution ?? 'Préstamo' }}
                                    @if($product->asset_type)
                                        - {{ $product->asset_type }}
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('products.index') }}" class="btn btn-sm btn-outline-secondary" wire:navigate>
                                <i class="fas fa-arrow-left me-1"></i>Volver
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Monto del Préstamo -->
                            <div class="col-md-3 mb-3">
                                <div class="d-flex flex-column">
                                    <span class="text-xs text-secondary">Monto del Préstamo</span>
                                    <h5 class="font-weight-bold mb-0">${{ number_format($summary['loan_amount_dollars'], 2) }}</h5>
                                </div>
                            </div>

                            <!-- Cuota Mensual -->
                            <div class="col-md-3 mb-3">
                                <div class="d-flex flex-column">
                                    <span class="text-xs text-secondary">Cuota Mensual</span>
                                    <h5 class="font-weight-bold mb-0 text-info">${{ number_format($summary['monthly_payment_dollars'], 2) }}</h5>
                                </div>
                            </div>

                            <!-- Tasa de Interés -->
                            <div class="col-md-2 mb-3">
                                <div class="d-flex flex-column">
                                    <span class="text-xs text-secondary">Tasa (TEA)</span>
                                    <h5 class="font-weight-bold mb-0">{{ number_format($summary['interest_rate'], 2) }}%</h5>
                                </div>
                            </div>

                            <!-- Plazo -->
                            <div class="col-md-2 mb-3">
                                <div class="d-flex flex-column">
                                    <span class="text-xs text-secondary">Plazo</span>
                                    <h5 class="font-weight-bold mb-0">{{ $summary['loan_term_months'] }} meses</h5>
                                </div>
                            </div>

                            <!-- Progreso -->
                            <div class="col-md-2 mb-3">
                                <div class="d-flex flex-column">
                                    <span class="text-xs text-secondary">Progreso</span>
                                    <h5 class="font-weight-bold mb-0 text-success">{{ number_format($summary['progress_percentage'], 0) }}%</h5>
                                </div>
                            </div>
                        </div>

                        <!-- Barra de Progreso -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-xs">{{ $summary['payments_made'] }} de {{ $summary['loan_term_months'] }} cuotas pagadas</span>
                                <span class="text-xs font-weight-bold">{{ $summary['remaining_payments'] }} pendientes</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-gradient-success"
                                     role="progressbar"
                                     style="width: {{ $summary['progress_percentage'] }}%"
                                     aria-valuenow="{{ $summary['progress_percentage'] }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <!-- Estadísticas Adicionales -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <span class="text-xs text-secondary">Total Pagado</span>
                                <p class="text-sm font-weight-bold text-success mb-0">${{ number_format($summary['total_paid_dollars'], 2) }}</p>
                            </div>
                            <div class="col-md-3">
                                <span class="text-xs text-secondary">Por Pagar</span>
                                <p class="text-sm font-weight-bold text-warning mb-0">${{ number_format($summary['remaining_amount_dollars'], 2) }}</p>
                            </div>
                            <div class="col-md-3">
                                <span class="text-xs text-secondary">Total Intereses</span>
                                <p class="text-sm font-weight-bold text-info mb-0">${{ number_format($summary['total_interest_dollars'], 2) }}</p>
                            </div>
                            <div class="col-md-3">
                                <span class="text-xs text-secondary">Próximo Pago</span>
                                <p class="text-sm font-weight-bold mb-0">
                                    @if($summary['next_payment_date'])
                                        {{ \Carbon\Carbon::parse($summary['next_payment_date'])->format('d/m/Y') }}
                                        @if($summary['is_overdue'])
                                            <span class="badge badge-sm bg-gradient-danger ms-1">Vencido</span>
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cronograma de Pagos -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Cronograma de Pagos</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cuota</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Fecha</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Cuota</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Capital</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Interés</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Saldo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($schedule as $payment)
                                        <tr class="{{ $payment['is_paid'] ? 'bg-light' : ($payment['is_overdue'] ? 'bg-gradient-danger text-white' : '') }}">
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0 ps-3">
                                                    #{{ $payment['payment_number'] }}
                                                </p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ $payment['payment_date_formatted'] }}
                                                </p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">
                                                    ${{ number_format($payment['monthly_payment_dollars'], 2) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs">
                                                    ${{ number_format($payment['principal_dollars'], 2) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs text-info">
                                                    ${{ number_format($payment['interest_dollars'], 2) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs">
                                                    ${{ number_format($payment['remaining_balance_dollars'], 2) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($payment['is_paid'])
                                                    <span class="badge badge-sm bg-gradient-success">Pagado</span>
                                                @elseif($payment['is_overdue'])
                                                    <span class="badge badge-sm bg-gradient-danger">
                                                        Vencido ({{ $payment['days_overdue'] }} días)
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
