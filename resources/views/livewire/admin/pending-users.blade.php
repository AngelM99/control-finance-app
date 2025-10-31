<div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Usuarios Pendientes de Aprobación</h6>
                        <span class="badge bg-gradient-warning">{{ $pendingUsers->total() }} Pendientes</span>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($pendingUsers->count() > 0)
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Usuario</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">DNI</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Teléfono</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Método</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Registro</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingUsers as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="icon icon-shape icon-sm bg-gradient-warning shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $user->dni }}</p>
                                            </td>
                                            <td>
                                                <p class="text-xs mb-0">{{ $user->phone ?? '-' }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if($user->google_id)
                                                    <span class="badge badge-sm bg-gradient-info">
                                                        <i class="fab fa-google me-1"></i>Google
                                                    </span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">
                                                        <i class="fas fa-envelope me-1"></i>Email
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0">
                                                    {{ $user->created_at->format('d/m/Y') }}
                                                </p>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $user->created_at->diffForHumans() }}
                                                </p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <button wire:click="approve({{ $user->id }})"
                                                            wire:confirm="¿Aprobar acceso para {{ $user->name }}?"
                                                            class="btn btn-sm bg-gradient-success mb-0"
                                                            wire:loading.attr="disabled">
                                                        <i class="fas fa-check me-1"></i>
                                                        <span wire:loading.remove wire:target="approve({{ $user->id }})">Aprobar</span>
                                                        <span wire:loading wire:target="approve({{ $user->id }})">
                                                            <span class="spinner-border spinner-border-sm"></span>
                                                        </span>
                                                    </button>
                                                    <button wire:click="reject({{ $user->id }})"
                                                            wire:confirm="¿Rechazar y eliminar cuenta de {{ $user->name }}?"
                                                            class="btn btn-sm btn-outline-danger mb-0"
                                                            wire:loading.attr="disabled">
                                                        <i class="fas fa-times me-1"></i>
                                                        <span wire:loading.remove wire:target="reject({{ $user->id }})">Rechazar</span>
                                                        <span wire:loading wire:target="reject({{ $user->id }})">
                                                            <span class="spinner-border spinner-border-sm"></span>
                                                        </span>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 mt-3">
                            {{ $pendingUsers->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="icon icon-shape icon-lg bg-gradient-success shadow mx-auto mb-3">
                                <i class="fas fa-check-circle opacity-10"></i>
                            </div>
                            <h6 class="text-secondary mb-0">No hay usuarios pendientes de aprobación</h6>
                            <p class="text-xs text-secondary mb-0 mt-2">Todos los usuarios registrados han sido procesados</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Stats Card -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md me-3">
                                    <i class="fas fa-users text-lg opacity-10"></i>
                                </div>
                                <div>
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Usuarios</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $totalUsers }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md me-3">
                                    <i class="fas fa-user-check text-lg opacity-10"></i>
                                </div>
                                <div>
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Usuarios Activos</p>
                                    <h5 class="font-weight-bolder mb-0">{{ $activeUsers }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
