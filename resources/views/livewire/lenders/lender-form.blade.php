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

                    <form wire:submit="save">
                        <div class="row">
                            <!-- Nombre Completo -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('full_name') is-invalid @enderror"
                                       wire:model="full_name"
                                       placeholder="Juan Pérez García">
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- DNI / Documento -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">DNI / Documento de Identidad <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('document_id') is-invalid @enderror"
                                       wire:model="document_id"
                                       placeholder="12345678">
                                @error('document_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
</div>
