<div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">{{ $productId ? 'Editar' : 'Nuevo' }} Producto Financiero</h6>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit="save">
                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre del Producto</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       wire:model="name" placeholder="Ej: Tarjeta Visa Gold">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Product Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Producto</label>
                                <select class="form-select @error('product_type') is-invalid @enderror" wire:model="product_type">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="credit_card">Tarjeta de Crédito</option>
                                    <option value="debit_card">Tarjeta de Débito</option>
                                    <option value="digital_wallet">Billetera Digital</option>
                                    <option value="credit_line">Línea de Crédito</option>
                                </select>
                                @error('product_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Account Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número de Cuenta</label>
                                <input type="text" class="form-control @error('account_number') is-invalid @enderror"
                                       wire:model="account_number" placeholder="****1234">
                                @error('account_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Credit Limit -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Límite de Crédito</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror"
                                           wire:model="credit_limit" placeholder="0.00">
                                </div>
                                @error('credit_limit')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Dejar en 0 para productos sin límite (billeteras, débito)</small>
                            </div>

                            <!-- Current Balance -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Saldo Actual</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('current_balance') is-invalid @enderror"
                                           wire:model="current_balance" placeholder="0.00">
                                </div>
                                @error('current_balance')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Interest Rate -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tasa de Interés (%)</label>
                                <input type="number" step="0.01" class="form-control @error('interest_rate') is-invalid @enderror"
                                       wire:model="interest_rate" placeholder="0.00">
                                @error('interest_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Opcional, para productos con interés</small>
                            </div>

                            <!-- Active Status -->
                            <div class="col-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                                    <label class="form-check-label" for="is_active">
                                        Producto Activo
                                    </label>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Descripción (Opcional)</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          wire:model="description" rows="3"
                                          placeholder="Detalles adicionales del producto..."></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary mb-0" wire:navigate>
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn bg-gradient-primary mb-0" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-save me-2"></i>{{ $productId ? 'Actualizar' : 'Guardar' }}
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
