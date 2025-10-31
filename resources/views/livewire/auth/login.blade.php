<div>
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <span class="alert-text text-white">{{ session('error') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <span class="alert-text text-white">{{ session('success') }}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form wire:submit="login" role="form">
        <x-honeypot livewire-model="honeypotData" />

        <!-- Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                wire:model="email"
                placeholder="ejemplo@email.com"
                autocomplete="email"
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                wire:model="password"
                placeholder="Contraseña"
                autocomplete="current-password"
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" id="remember" wire:model="remember">
            <label class="form-check-label" for="remember">Recordarme</label>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button
                type="submit"
                class="btn bg-gradient-primary w-100 mb-0"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Iniciar Sesión</span>
                <span wire:loading>
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    Iniciando...
                </span>
            </button>
        </div>

        <!-- Divider -->
        <div class="my-4 text-center">
            <span class="text-muted text-sm">O continuar con</span>
        </div>

        <!-- Google OAuth -->
        <div class="text-center">
            <a href="{{ route('auth.google') }}" class="btn btn-outline-secondary w-100 mb-3">
                <svg class="me-2" width="20" height="20" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Iniciar sesión con Google
            </a>
        </div>

        <!-- Register Link -->
        <p class="text-sm mt-4 mb-0 text-center">
            ¿No tienes una cuenta?
            <a href="{{ route('register') }}" class="text-primary text-gradient font-weight-bold">Regístrate aquí</a>
        </p>
    </form>
</div>
