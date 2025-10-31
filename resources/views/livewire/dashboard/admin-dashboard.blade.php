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
                                   title="Total Usuarios">Total Usuarios</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalUsers }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow border-radius-md">
                                <i class="fas fa-users text-lg opacity-10"></i>
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
                                   title="Aprobaciones Pendientes">Aprobaciones Pendientes</p>
                                <h5 class="font-weight-bolder mb-0 text-warning">
                                    {{ $pendingApprovals }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow border-radius-md">
                                <i class="fas fa-user-clock text-lg opacity-10"></i>
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
                                   title="Total Productos">Total Productos</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalProducts }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow border-radius-md">
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
                                   title="Total Transacciones">Total Transacciones</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $totalTransactions }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow border-radius-md">
                                <i class="fas fa-exchange-alt text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 content-card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Usuarios Recientes</h6>
                        @if($pendingApprovals > 0)
                            <a href="{{ route('admin.pending-users') }}" class="badge bg-gradient-warning" wire:navigate>
                                {{ $pendingApprovals }} Pendientes
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($recentUsers->count() > 0)
                        <div class="list-group">
                            @foreach($recentUsers as $user)
                                <div class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-dark shadow text-center">
                                            <i class="fas fa-user text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark text-sm">{{ $user->name }}</h6>
                                            <span class="text-xs">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="badge badge-sm {{ $user->is_approved ? 'bg-gradient-success' : 'bg-gradient-warning' }}">
                                            {{ $user->is_approved ? 'Aprobado' : 'Pendiente' }}
                                        </span>
                                        <span class="text-xs text-secondary mt-1">{{ $user->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-0">No hay usuarios registrados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100 content-card">
                <div class="card-header pb-0">
                    <h6>Transacciones Recientes</h6>
                </div>
                <div class="card-body">
                    @if($recentTransactions->count() > 0)
                        <div class="list-group">
                            @foreach($recentTransactions as $transaction)
                                <div class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                                            <i class="fas fa-exchange-alt text-white opacity-10"></i>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <h6 class="mb-1 text-dark text-sm">{{ $transaction->user?->name ?? 'N/A' }}</h6>
                                            <span class="text-xs">{{ $transaction->financialProduct?->name ?? 'N/A' }} - {{ ucfirst($transaction->transaction_type) }}</span>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="text-dark font-weight-bold text-sm">${{ number_format($transaction->amount / 100, 2) }}</span>
                                        <span class="text-xs text-secondary">{{ $transaction->transaction_date->format('d/m/Y') }}</span>
                                    </div>
                                </div>
                            @endforeach
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
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="{{ route('admin.pending-users') }}" class="btn bg-gradient-warning w-100 mb-0 action-btn" wire:navigate>
                                <i class="fas fa-user-clock me-2"></i>Gestionar Usuarios Pendientes
                                @if($pendingApprovals > 0)
                                    <span class="badge bg-white text-dark ms-2">{{ $pendingApprovals }}</span>
                                @endif
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="{{ route('products.index') }}" class="btn bg-gradient-primary w-100 mb-0 action-btn" wire:navigate>
                                <i class="fas fa-credit-card me-2"></i>Ver Todos los Productos
                            </a>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <a href="{{ route('transactions.index') }}" class="btn bg-gradient-success w-100 mb-0 action-btn" wire:navigate>
                                <i class="fas fa-exchange-alt me-2"></i>Ver Todas las Transacciones
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
