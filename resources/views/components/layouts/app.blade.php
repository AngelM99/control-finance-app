<x-layouts.base>
    @auth
        <!-- Sidebar -->
        <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
            <!-- Botón cerrar para móvil -->
            <button class="sidenav-close d-xl-none" id="sidenav-close-btn">
                <i class="fas fa-times"></i>
            </button>

            <div class="sidenav-header">
                <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
                <a class="align-items-center d-flex m-0 navbar-brand text-wrap" href="{{ route('dashboard') }}">
                    <img src="/assets/img/logo-ct.png" class="navbar-brand-img h-100" alt="logo">
                    <span class="ms-3 font-weight-bold">Control Finance</span>
                </a>
            </div>
            <hr class="horizontal dark mt-0">
            <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
                <ul class="navbar-nav">
                    <li class="nav-item pb-2">
                        <a class="nav-link {{ request()->routeIs('dashboard') || request()->routeIs('user.dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-home {{ request()->routeIs('dashboard') || request()->routeIs('user.dashboard') || request()->routeIs('admin.dashboard') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item mt-2">
                        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Gestión Financiera</h6>
                    </li>

                    @can('view own financial products')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-credit-card {{ request()->routeIs('products.*') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Productos Financieros</span>
                        </a>
                    </li>
                    @endcan

                    @can('view own transactions')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-exchange-alt {{ request()->routeIs('transactions.*') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Transacciones</span>
                        </a>
                    </li>
                    @endcan

                    @can('view own installments')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('installments.*') ? 'active' : '' }}" href="{{ route('installments.index') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-calendar-alt {{ request()->routeIs('installments.*') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Cuotas</span>
                        </a>
                    </li>
                    @endcan

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('lenders.*') ? 'active' : '' }}" href="{{ route('lenders.index') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-users {{ request()->routeIs('lenders.*') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Prestamistas</span>
                        </a>
                    </li>

                    {{-- Sección de Reportes --}}
                    <li class="nav-item mt-2">
                        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Reportes</h6>
                    </li>

                    @can('approve users')
                    {{-- ADMIN: Reporte Global de Prestamistas --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.borrowers') ? 'active' : '' }}" href="{{ route('reports.borrowers') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-chart-bar {{ request()->routeIs('reports.borrowers') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Reporte Global de Prestamistas</span>
                        </a>
                    </li>

                    {{-- ADMIN: Mis Prestamistas (solo admin) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.my-borrowers') ? 'active' : '' }}" href="{{ route('reports.my-borrowers') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-users-cog {{ request()->routeIs('reports.my-borrowers') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Mis Prestamistas</span>
                        </a>
                    </li>
                    @endcan

                    {{-- COMÚN: Reporte de Transacciones (para todos los usuarios autenticados) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.transactions') ? 'active' : '' }}" href="{{ route('reports.transactions') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-file-invoice {{ request()->routeIs('reports.transactions') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Reporte de Transacciones</span>
                        </a>
                    </li>

                    {{-- COMÚN: Estado de Cuenta por Prestamista (para todos los usuarios autenticados) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.account-statement') ? 'active' : '' }}" href="{{ route('reports.account-statement') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-file-alt {{ request()->routeIs('reports.account-statement') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Estado de Cuenta</span>
                        </a>
                    </li>

                    {{-- COMÚN: Reporte de Pagos por Tarjeta (para todos los usuarios autenticados) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('reports.card-payments') ? 'active' : '' }}" href="{{ route('reports.card-payments') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-credit-card {{ request()->routeIs('reports.card-payments') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Pagos por Tarjeta</span>
                        </a>
                    </li>

                    @can('approve users')
                    <li class="nav-item mt-2">
                        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Administración</h6>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.pending-users') ? 'active' : '' }}" href="{{ route('admin.pending-users') }}" wire:navigate>
                            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
                                <i class="fas fa-user-clock {{ request()->routeIs('admin.pending-users') ? 'text-white' : 'text-dark' }}"></i>
                            </div>
                            <span class="nav-link-text ms-1">Usuarios Pendientes</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
            <!-- Navbar -->
            <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
                <div class="container-fluid py-1 px-3">
                    <!-- Botón hamburguesa para móvil -->
                    <button class="navbar-toggler shadow-none ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon mt-2">
                            <span class="navbar-toggler-bar bar1"></span>
                            <span class="navbar-toggler-bar bar2"></span>
                            <span class="navbar-toggler-bar bar3"></span>
                        </span>
                    </button>

                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="{{ route('dashboard') }}">Inicio</a></li>
                            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">{{ $title ?? 'Dashboard' }}</li>
                        </ol>
                        <h6 class="font-weight-bolder mb-0">{{ $title ?? 'Dashboard' }}</h6>
                    </nav>
                    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                        <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                        </div>
                        <ul class="navbar-nav justify-content-end">
                            <li class="nav-item d-flex align-items-center">
                                <span class="nav-link text-body font-weight-bold px-2">
                                    <i class="fa fa-user me-sm-1"></i>
                                    <span class="d-sm-inline d-none">{{ Auth::user()->name }}</span>
                                </span>
                            </li>
                            <li class="nav-item d-flex align-items-center">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="nav-link text-body font-weight-bold px-0" style="background: none; border: none;">
                                        <i class="fa fa-sign-out-alt"></i>
                                        <span class="d-sm-inline d-none">Salir</span>
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <div class="container-fluid py-4">
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <span class="alert-text text-white">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <span class="alert-text text-white">{{ session('error') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    @endauth

    @guest
        {{ $slot }}
    @endguest
</x-layouts.base>
