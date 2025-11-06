<div>
    <!-- Stats Grid -->
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row w-100">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold stats-title"
                                   data-bs-toggle="tooltip"
                                   title="Total Productos">Total Productos</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalProducts }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow border-radius-md">
                                <i class="fas fa-credit-card text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row w-100">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold stats-title"
                                   data-bs-toggle="tooltip"
                                   title="Productos Activos">Productos Activos</p>
                                <h5 class="font-weight-bolder mb-0 text-success">
                                    {{ $activeProducts }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow border-radius-md">
                                <i class="fas fa-check-circle text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row w-100">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold stats-title"
                                   data-bs-toggle="tooltip"
                                   title="Saldo Total">Saldo Total</p>
                                <h5 class="font-weight-bolder mb-0">
                                    S/ {{ number_format($totalBalance / 100, 2) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow border-radius-md">
                                <i class="fas fa-wallet text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stats-card">
                <div class="card-body p-3">
                    <div class="row w-100">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold stats-title"
                                   data-bs-toggle="tooltip"
                                   title="Cuotas Pendientes">Cuotas Pendientes</p>
                                <h5 class="font-weight-bolder mb-0 text-warning">
                                    {{ $pendingInstallments }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow border-radius-md">
                                <i class="fas fa-clock text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Credit Cards Billing Summary -->
    @if(count($creditCardsSummary) > 0)
        <div class="row mt-4">
            <div class="col-12">
                <h6 class="mb-3">Resumen de Ciclos de Facturación</h6>
            </div>
            @foreach($creditCardsSummary as $cardData)
                @php
                    $product = $cardData['product'];
                    $summary = $cardData['summary'];
                    $usagePercent = $product->credit_usage_percentage;
                    $progressColor = $usagePercent > 80 ? 'danger' : ($usagePercent > 50 ? 'warning' : 'success');
                @endphp
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header pb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $product->name }}</h6>
                                <span class="badge badge-sm bg-gradient-{{ $progressColor }}">
                                    {{ number_format($usagePercent, 0) }}% usado
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Período de Facturación -->
                            <div class="mb-3">
                                <p class="text-xs text-secondary mb-1">Período Actual</p>
                                <p class="text-sm font-weight-bold mb-0">
                                    <i class="fas fa-calendar me-1"></i>
                                    {{ $summary['period_start']->format('d/m/Y') }} - {{ $summary['period_end']->format('d/m/Y') }}
                                </p>
                            </div>

                            <!-- Fecha de Pago -->
                            <div class="mb-3">
                                <p class="text-xs text-secondary mb-1">Fecha de Pago</p>
                                <p class="text-sm font-weight-bold mb-0">
                                    <i class="fas fa-calendar-check me-1 text-success"></i>
                                    {{ $summary['payment_due_date']->format('d/m/Y') }}
                                    <span class="text-xs text-secondary ms-2">({{ $summary['days_until_payment'] }} días)</span>
                                </p>
                            </div>

                            <!-- Balance del Período -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-xs text-secondary mb-1">Balance del Período</p>
                                        <p class="text-sm font-weight-bold mb-0">
                                            S/ {{ number_format($summary['period_balance'] / 100, 2) }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <p class="text-xs text-secondary mb-1">Disponible</p>
                                        <p class="text-sm font-weight-bold mb-0 text-success">
                                            S/ {{ number_format($summary['available_credit'] / 100, 2) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Barra de Progreso -->
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-xs">Límite de Crédito</span>
                                    <span class="text-xs font-weight-bold">S/ {{ number_format($product->credit_limit / 100, 2) }}</span>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-gradient-{{ $progressColor }}"
                                         role="progressbar"
                                         style="width: {{ $usagePercent }}%"
                                         aria-valuenow="{{ $usagePercent }}"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Recent Transactions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card content-card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Transacciones Recientes</h6>
                        <a href="{{ route('transactions.index') }}" class="btn btn-sm bg-gradient-primary mb-0" wire:navigate>
                            Ver Todas
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($recentTransactions->count() > 0)
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fecha</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Producto</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipo</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0 ps-3">{{ $transaction->transaction_date->format('d/m/Y') }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $transaction->financialProduct?->name ?? 'N/A' }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-info">{{ ucfirst($transaction->transaction_type) }}</span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs font-weight-bold">S/ {{ number_format($transaction->amount / 100, 2) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-0">No hay transacciones registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('products.create') }}" class="btn bg-gradient-primary w-100 mb-0 action-btn" wire:navigate>
                <i class="fas fa-plus me-2"></i>Nuevo Producto Financiero
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('transactions.create') }}" class="btn bg-gradient-success w-100 mb-0 action-btn" wire:navigate>
                <i class="fas fa-plus me-2"></i>Nueva Transacción
            </a>
        </div>
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('installments.index') }}" class="btn bg-gradient-info w-100 mb-0 action-btn" wire:navigate>
                <i class="fas fa-calendar-alt me-2"></i>Ver Cuotas
            </a>
        </div>
    </div>
</div>
