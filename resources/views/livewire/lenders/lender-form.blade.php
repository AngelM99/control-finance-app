<div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">{{ $lender ? 'Editar' : 'Nuevo' }} Prestamista</h6>
                    </div>
                </div>
                <div class="card-body">
                    @error('general')
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <strong>Error:</strong> {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror

                    <!-- Contenedor de alertas -->
                    <div id="alert-container" class="mb-3"></div>

                    <form wire:submit="save">
                        <div class="row">
                            <!-- DNI / Documento con Búsqueda -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">DNI / Documento de Identidad <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control @error('document_id') is-invalid @enderror"
                                           wire:model="document_id"
                                           placeholder="12345678"
                                           maxlength="8">
                                    <button type="button"
                                            class="btn btn-outline-primary mb-0"
                                            wire:click="searchDni"
                                            wire:loading.attr="disabled"
                                            wire:target="searchDni"
                                            title="Buscar datos del DNI en SUNAT">
                                        <span wire:loading.remove wire:target="searchDni">
                                            <i class="fas fa-search"></i> Buscar
                                        </span>
                                        <span wire:loading wire:target="searchDni">
                                            <span class="spinner-border spinner-border-sm"></span> Buscando...
                                        </span>
                                    </button>
                                </div>
                                @error('document_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Ingrese el DNI y haga clic en "Buscar" para autocompletar el nombre
                                </small>
                            </div>

                            <!-- Nombre Completo -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('full_name') is-invalid @enderror"
                                       wire:model="full_name"
                                       placeholder="Juan Pérez García">
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>Se completará automáticamente al buscar el DNI
                                </small>
                            </div>

                            <!-- Teléfono -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Teléfono (Opcional)</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       wire:model="phone"
                                       placeholder="+51 987654321">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Correo Electrónico (Opcional)</label>
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       wire:model="email"
                                       placeholder="ejemplo@correo.com">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Observaciones -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Observaciones (Opcional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror"
                                          wire:model="notes"
                                          rows="3"
                                          placeholder="Notas adicionales sobre este prestamista..."></textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Estado -->
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="isActive"
                                           wire:model="is_active">
                                    <label class="form-check-label" for="isActive">
                                        <i class="fas fa-check-circle me-2"></i>Prestamista activo
                                    </label>
                                </div>
                                <small class="text-muted">Si está inactivo, no aparecerá en los selectores al crear transacciones.</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('lenders.index') }}"
                               class="btn btn-outline-secondary mb-0"
                               wire:navigate>
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit"
                                    class="btn bg-gradient-success mb-0"
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-save me-2"></i>{{ $lender ? 'Actualizar' : 'Guardar' }}
                                </span>
                                <span wire:loading>
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Guardando...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Escuchar el evento de alertas de Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-alert', (data) => {
                const alertData = Array.isArray(data) ? data[0] : data;
                showAlert(alertData.type, alertData.message);
            });
        });

        function showAlert(type, message) {
            const container = document.getElementById('alert-container');

            // Mapeo de tipos a clases de Bootstrap
            const alertTypes = {
                'success': 'alert-success',
                'error': 'alert-danger',
                'warning': 'alert-warning',
                'info': 'alert-info'
            };

            // Mapeo de iconos
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };

            const alertClass = alertTypes[type] || 'alert-info';
            const icon = icons[type] || 'fa-info-circle';

            // Crear el HTML de la alerta
            const alertHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    <i class="fas ${icon} me-2"></i>
                    <strong>${message}</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;

            // Limpiar alertas anteriores
            container.innerHTML = '';

            // Agregar la nueva alerta
            container.innerHTML = alertHTML;

            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                const alert = container.querySelector('.alert');
                if (alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);
        }
    </script>
    @endpush
</div>
