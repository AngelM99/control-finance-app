<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0 text-center">
                    <h4 class="font-weight-bolder text-primary">Consulta Pública</h4>
                    <p class="mb-0 text-sm">
                        Consulta tus productos financieros o transacciones sin necesidad de iniciar sesión
                    </p>
                </div>

                <div class="card-body">
                    @if (session()->has('otp_generated'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <span class="alert-text text-white">{{ session('otp_generated') }}</span>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <x-honeypot livewire-model="honeypotData" />

                    {{-- Step 1: DNI Input --}}
                    @if ($step === 'dni')
                        {{-- Consultation Type Selector --}}
                        <div class="mb-4">
                            <label class="form-label font-weight-bold">Tipo de Consulta</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="form-check card p-3 {{ $consultationType === 'owner' ? 'bg-gradient-primary' : 'border' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="consultationType" value="owner" id="typeOwner">
                                        <label class="form-check-label {{ $consultationType === 'owner' ? 'text-white' : '' }}" for="typeOwner">
                                            <i class="fas fa-user-tie me-2"></i>
                                            <strong>Soy Propietario</strong>
                                            <p class="text-xs mb-0 mt-1 {{ $consultationType === 'owner' ? 'text-white' : 'text-secondary' }}">
                                                Consulta tus productos con código OTP
                                            </p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check card p-3 {{ $consultationType === 'lender' ? 'bg-gradient-warning' : 'border' }}">
                                        <input class="form-check-input" type="radio" wire:model.live="consultationType" value="lender" id="typeLender">
                                        <label class="form-check-label {{ $consultationType === 'lender' ? 'text-white' : '' }}" for="typeLender">
                                            <i class="fas fa-users me-2"></i>
                                            <strong>Soy Usuario Autorizado</strong>
                                            <p class="text-xs mb-0 mt-1 {{ $consultationType === 'lender' ? 'text-white' : 'text-secondary' }}">
                                                Consulta tus transacciones sin OTP
                                            </p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Owner Form --}}
                        @if ($consultationType === 'owner')
                        <form wire:submit="requestOtp">
                            <div class="mb-3">
                                <label for="dni" class="form-label">Ingrese su DNI</label>
                                <input
                                    type="text"
                                    id="dni"
                                    wire:model="dni"
                                    class="form-control @error('dni') is-invalid @enderror"
                                    placeholder="12345678"
                                    autocomplete="off"
                                >
                                @error('dni')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="btn bg-gradient-success w-100 mb-0"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>Solicitar Código OTP</span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Generando código...
                                </span>
                            </button>

                            <div class="mt-3 text-center">
                                <a href="{{ route('login') }}" class="text-sm text-primary">
                                    ¿Prefieres iniciar sesión?
                                </a>
                            </div>
                        </form>
                        @endif

                        {{-- Lender Form --}}
                        @if ($consultationType === 'lender')
                        <form wire:submit="consultLender">
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                <span class="text-sm">
                                    Si eres usuario autorizado, ingresa tu DNI para ver las transacciones que has realizado.
                                </span>
                            </div>

                            <div class="mb-3">
                                <label for="dni" class="form-label">Ingrese su DNI</label>
                                <input
                                    type="text"
                                    id="dni"
                                    wire:model="dni"
                                    class="form-control @error('dni') is-invalid @enderror"
                                    placeholder="12345678"
                                    autocomplete="off"
                                >
                                @error('dni')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="btn bg-gradient-warning w-100 mb-0"
                                wire:loading.attr="disabled"
                            >
                                <span wire:loading.remove>
                                    <i class="fas fa-search me-2"></i>Consultar Transacciones
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Consultando...
                                </span>
                            </button>

                            <div class="mt-3 text-center">
                                <a href="{{ route('login') }}" class="text-sm text-primary">
                                    ¿Prefieres iniciar sesión?
                                </a>
                            </div>
                        </form>
                        @endif
                    @endif

                    {{-- Step 2: OTP Verification --}}
                    @if ($step === 'otp')
                        <form wire:submit="verifyOtp">
                            <div class="alert alert-info" role="alert">
                                <span class="text-sm">
                                    Se ha generado un código OTP para el DNI: <strong>{{ $dni }}</strong><br>
                                    En producción, este código se enviaría por SMS o correo electrónico.
                                </span>
                            </div>

                            <div class="mb-3">
                                <label for="otp" class="form-label">Código OTP (6 dígitos)</label>
                                <input
                                    type="text"
                                    id="otp"
                                    wire:model="otp"
                                    class="form-control text-center fs-4 @error('otp') is-invalid @enderror"
                                    placeholder="000000"
                                    maxlength="6"
                                    autocomplete="off"
                                    style="letter-spacing: 0.5rem;"
                                >
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2">
                                <button
                                    type="submit"
                                    class="btn bg-gradient-success"
                                    wire:loading.attr="disabled"
                                >
                                    <span wire:loading.remove>Verificar Código</span>
                                    <span wire:loading>
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Verificando...
                                    </span>
                                </button>

                                <button
                                    type="button"
                                    wire:click="resetForm"
                                    class="btn btn-outline-secondary"
                                >
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    @endif

                    {{-- Step 3: Results --}}
                    @if ($step === 'results' && $user)
                        <div>
                            <div class="mb-4 pb-3 border-bottom">
                                <h5 class="font-weight-bolder">Información del Usuario</h5>
                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">Nombre</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $user->name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">DNI</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $user->dni }}</p>
                                    </div>
                                    @if($user->email)
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">Email</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $user->email }}</p>
                                    </div>
                                    @endif
                                    @if($user->phone)
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">Teléfono</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $user->phone }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="font-weight-bolder mb-3">Productos Financieros</h5>

                                @if($financialProducts->count() > 0)
                                    @foreach($financialProducts as $product)
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="font-weight-bold mb-0">{{ $product->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            {{ ucfirst(str_replace('_', ' ', $product->product_type)) }}
                                                        </p>
                                                    </div>
                                                    <span class="badge badge-sm {{ $product->is_active ? 'bg-gradient-success' : 'bg-gradient-danger' }}">
                                                        {{ $product->is_active ? 'Activo' : 'Inactivo' }}
                                                    </span>
                                                </div>

                                                <div class="row mb-3">
                                                    @if($product->credit_limit > 0)
                                                    <div class="col-6">
                                                        <p class="text-xs text-secondary mb-1">Límite de Crédito</p>
                                                        <p class="text-sm font-weight-bold mb-0">S/ {{ number_format($product->credit_limit_in_dollars, 2) }}</p>
                                                    </div>
                                                    @endif
                                                    <div class="col-6">
                                                        <p class="text-xs text-secondary mb-1">Saldo Actual</p>
                                                        <p class="text-sm font-weight-bold mb-0">S/ {{ number_format($product->current_balance_in_dollars, 2) }}</p>
                                                    </div>
                                                </div>

                                                @if($product->transactions && $product->transactions->count() > 0)
                                                    <div class="mt-3 pt-3 border-top">
                                                        <p class="text-xs font-weight-bold text-secondary mb-2">Últimas Transacciones</p>
                                                        @foreach($product->transactions as $transaction)
                                                            <div class="d-flex justify-content-between mb-2">
                                                                <span class="text-xs">
                                                                    {{ $transaction->transaction_date->format('d/m/Y') }} -
                                                                    {{ ucfirst($transaction->transaction_type) }}
                                                                </span>
                                                                <span class="text-xs font-weight-bold">
                                                                    S/ {{ number_format($transaction->amount_in_dollars, 2) }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="alert alert-secondary text-center" role="alert">
                                        <p class="mb-0">No se encontraron productos financieros registrados.</p>
                                    </div>
                                @endif
                            </div>

                            <button
                                wire:click="resetForm"
                                class="btn btn-outline-secondary w-100 mb-0"
                            >
                                Nueva Consulta
                            </button>
                        </div>
                    @endif

                    {{-- Step 3: Lender Results --}}
                    @if ($step === 'results' && $lender)
                        <div>
                            <div class="mb-4 pb-3 border-bottom">
                                <h5 class="font-weight-bolder">
                                    <i class="fas fa-user-check text-warning me-2"></i>
                                    Información del Usuario Autorizado
                                </h5>
                                <div class="row mt-3">
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">Nombre Completo</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $lender->full_name }}</p>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">DNI</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $lender->document_id }}</p>
                                    </div>
                                    @if($lender->phone)
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">Teléfono</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $lender->phone }}</p>
                                    </div>
                                    @endif
                                    @if($lender->email)
                                    <div class="col-md-6 mb-3">
                                        <p class="text-xs text-uppercase text-secondary mb-1">Email</p>
                                        <p class="text-sm font-weight-bold mb-0">{{ $lender->email }}</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-4">
                                <h5 class="font-weight-bolder mb-3">
                                    <i class="fas fa-list text-warning me-2"></i>
                                    Mis Transacciones
                                </h5>

                                @if($transactions->count() > 0)
                                    <div class="alert alert-info" role="alert">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <span class="text-sm">
                                            Total de transacciones encontradas: <strong>{{ $transactions->count() }}</strong>
                                        </span>
                                    </div>

                                    @foreach($transactions as $transaction)
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <div>
                                                        <h6 class="font-weight-bold mb-1">
                                                            {{ $transaction->financialProduct->name }}
                                                        </h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <i class="fas fa-user-tie me-1"></i>
                                                            Propietario: <strong>{{ $transaction->financialProduct->user->name }}</strong>
                                                        </p>
                                                    </div>
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
                                                </div>

                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <p class="text-xs text-secondary mb-1">Fecha</p>
                                                        <p class="text-sm font-weight-bold mb-0">
                                                            {{ $transaction->transaction_date->format('d/m/Y H:i') }}
                                                        </p>
                                                    </div>
                                                    <div class="col-6">
                                                        <p class="text-xs text-secondary mb-1">Monto</p>
                                                        <p class="text-sm font-weight-bold mb-0 text-{{ $color }}">
                                                            S/ {{ number_format($transaction->amount / 100, 2) }}
                                                        </p>
                                                    </div>
                                                </div>

                                                @if($transaction->description)
                                                <div class="mb-2">
                                                    <p class="text-xs text-secondary mb-1">Descripción</p>
                                                    <p class="text-sm mb-0">{{ $transaction->description }}</p>
                                                </div>
                                                @endif

                                                @if($transaction->merchant)
                                                <div class="mb-2">
                                                    <p class="text-xs text-secondary mb-1">Comercio</p>
                                                    <p class="text-sm mb-0">
                                                        <i class="fas fa-store me-1"></i>{{ $transaction->merchant }}
                                                    </p>
                                                </div>
                                                @endif

                                                @if($transaction->reference_number)
                                                <div>
                                                    <p class="text-xs text-secondary mb-1">Referencia</p>
                                                    <p class="text-sm mb-0 font-monospace">{{ $transaction->reference_number }}</p>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach

                                    <div class="card bg-gradient-light">
                                        <div class="card-body text-center">
                                            <p class="text-sm mb-2">
                                                <i class="fas fa-calculator me-2"></i>
                                                <strong>Total de transacciones:</strong>
                                            </p>
                                            <p class="text-lg font-weight-bold mb-0">
                                                S/ {{ number_format($transactions->sum('amount') / 100, 2) }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-secondary text-center" role="alert">
                                        <i class="fas fa-inbox me-2"></i>
                                        <p class="mb-0">No se encontraron transacciones registradas a tu nombre.</p>
                                    </div>
                                @endif
                            </div>

                            <button
                                wire:click="resetForm"
                                class="btn btn-outline-secondary w-100 mb-0"
                            >
                                Nueva Consulta
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
