<x-layouts.guest>
    <x-slot name="title">Bienvenido</x-slot>

    <div class="text-center mb-4">
        <h4 class="font-weight-bold mb-3">
            Bienvenido
        </h4>
        <p class="text-sm text-muted mb-0">
            Sistema de gestión y control de productos financieros personales
        </p>
    </div>

    <div class="d-grid gap-3 mb-3">
        <a href="{{ route('login') }}" class="btn bg-gradient-primary btn-lg w-100">
            <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
        </a>

        <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg w-100">
            <i class="fas fa-user-plus me-2"></i>Registrarse
        </a>

        <a href="{{ route('public.consultation') }}" class="btn bg-gradient-success btn-lg w-100">
            <i class="fas fa-search me-2"></i>Consulta Pública (Sin Login)
        </a>
    </div>

    <div class="text-center mt-4 pt-3 border-top">
        <p class="text-xs text-muted mb-0">
            <i class="fab fa-google me-1"></i>
            Autenticación con Google OAuth o registro manual
        </p>
    </div>
</x-layouts.guest>
