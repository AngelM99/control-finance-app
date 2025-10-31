<div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">{{ $product ? 'Editar' : 'Nuevo' }} Producto Financiero</h6>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit="save">
                            <!-- Información Básica -->
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nombre del Producto *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           wire:model="name" placeholder="Ej: Visa Platinum BBVA">
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tipo de Producto *</label>
                                    <select class="form-select @error('product_type') is-invalid @enderror"
                                            wire:model.live="product_type">
                                        <option value="">Seleccionar...</option>
                                        <option value="credit_card">💳 Tarjeta de Crédito</option>
                                        <option value="debit_card">💳 Tarjeta de Débito</option>
                                        <option value="savings_account">🏦 Cuenta de Ahorros</option>
                                        <option value="personal_loan">💰 Préstamo Personal</option>
                                        <option value="asset_loan">🚗 Crédito por Bien</option>
                                        <option value="digital_wallet">📱 Billetera Digital</option>
                                        <option value="credit_line">💵 Línea de Crédito</option>
                                    </select>
                                    @error('product_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Institución Financiera</label>
                                    <input type="text" class="form-control" wire:model="institution"
                                           placeholder="Ej: BBVA, BCP, Interbank">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Últimos 4 Dígitos</label>
                                    <input type="text" class="form-control" wire:model="last_four_digits"
                                           placeholder="1234" maxlength="4">
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Marca</label>
                                    <input type="text" class="form-control" wire:model="card_brand"
                                           placeholder="Visa, Mastercard, etc.">
                                </div>
                            </div>

                            <!-- Campos para Tarjetas de Crédito y Líneas de Crédito -->
                            @if(in_array($product_type, ['credit_card', 'credit_line']))
                                <hr class="my-4">
                                <h6 class="text-uppercase text-sm mb-3">Información de Crédito</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Límite de Crédito (USD) *</label>
                                        <input type="number" step="0.01" class="form-control @error('credit_limit') is-invalid @enderror"
                                               wire:model="credit_limit" placeholder="5000.00">
                                        @error('credit_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Saldo Actual (USD)</label>
                                        <input type="number" step="0.01" class="form-control"
                                               wire:model="current_balance" placeholder="0.00">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Fecha de Vencimiento</label>
                                        <input type="date" class="form-control" wire:model="expiration_date">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Día de Corte/Facturación</label>
                                        <input type="number" min="1" max="31" class="form-control"
                                               wire:model="billing_day" placeholder="20">
                                        <small class="text-muted">Día del mes en que cierra el ciclo</small>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Día de Pago</label>
                                        <input type="number" min="1" max="31" class="form-control"
                                               wire:model="payment_due_day" placeholder="15">
                                        <small class="text-muted">Día del mes para pagar</small>
                                    </div>
                                </div>
                            @endif

                            <!-- Campos para Tarjeta de Débito -->
                            @if($product_type === 'debit_card')
                                <hr class="my-4">
                                <h6 class="text-uppercase text-sm mb-3">Información de Débito</h6>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Saldo Disponible (USD) *</label>
                                        <input type="number" step="0.01" class="form-control @error('current_balance') is-invalid @enderror"
                                               wire:model="current_balance" placeholder="1000.00">
                                        @error('current_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Fecha de Vencimiento</label>
                                        <input type="date" class="form-control" wire:model="expiration_date">
                                    </div>
                                </div>
                            @endif

                            <!-- Campos para Cuenta de Ahorros -->
                            @if($product_type === 'savings_account')
                                <hr class="my-4">
                                <h6 class="text-uppercase text-sm mb-3">Información de Ahorro</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Saldo Actual (USD) *</label>
                                        <input type="number" step="0.01" class="form-control @error('current_balance') is-invalid @enderror"
                                               wire:model="current_balance" placeholder="5000.00">
                                        @error('current_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tasa de Interés Anual (%) *</label>
                                        <input type="number" step="0.01" class="form-control @error('interest_rate') is-invalid @enderror"
                                               wire:model="interest_rate" placeholder="3.50">
                                        @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        <small class="text-muted">TEA - Tasa efectiva anual</small>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Límite de Retiros/Mes</label>
                                        <input type="number" min="0" class="form-control"
                                               wire:model="monthly_withdrawal_limit" placeholder="4">
                                        <small class="text-muted">Dejar vacío = sin límite</small>
                                    </div>
                                </div>
                            @endif

                            <!-- Campos para Préstamos -->
                            @if(in_array($product_type, ['personal_loan', 'asset_loan']))
                                <hr class="my-4">
                                <h6 class="text-uppercase text-sm mb-3">Información del Préstamo</h6>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Monto del Préstamo (USD) *</label>
                                        <input type="number" step="0.01" class="form-control @error('loan_amount') is-invalid @enderror"
                                               wire:model.live="loan_amount" placeholder="10000.00">
                                        @error('loan_amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Plazo (Meses) *</label>
                                        <input type="number" min="1" max="360" class="form-control @error('loan_term_months') is-invalid @enderror"
                                               wire:model.live="loan_term_months" placeholder="24">
                                        @error('loan_term_months') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Tasa de Interés (% TEA) *</label>
                                        <input type="number" step="0.01" class="form-control @error('interest_rate') is-invalid @enderror"
                                               wire:model.live="interest_rate" placeholder="18.50">
                                        @error('interest_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Fecha de Inicio *</label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                               wire:model="start_date">
                                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Cuota Mensual (USD)</label>
                                        <input type="number" step="0.01" class="form-control bg-light"
                                               wire:model="monthly_payment" readonly>
                                        <small class="text-muted">Calculado automáticamente</small>
                                    </div>

                                    @if($product_type === 'asset_loan')
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tipo de Bien Financiado *</label>
                                            <input type="text" class="form-control @error('asset_type') is-invalid @enderror"
                                                   wire:model="asset_type" placeholder="Vehículo, Electrodoméstico, etc.">
                                            @error('asset_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>

                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Proveedor/Tienda</label>
                                            <input type="text" class="form-control"
                                                   wire:model="supplier" placeholder="Ej: Automotores S.A.">
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Notas y Estado -->
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Notas Adicionales</label>
                                    <textarea class="form-control" wire:model="notes" rows="3"
                                              placeholder="Información adicional sobre el producto..."></textarea>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="is_active">
                                        <label class="form-check-label" for="is_active">
                                            Producto Activo
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn bg-gradient-primary"
                                        wire:loading.attr="disabled">
                                    <span wire:loading.remove>
                                        <i class="fas fa-save me-2"></i>{{ $product ? 'Actualizar' : 'Crear' }} Producto
                                    </span>
                                    <span wire:loading>
                                        <i class="fas fa-spinner fa-spin me-2"></i>Guardando...
                                    </span>
                                </button>

                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary" wire:navigate>
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
