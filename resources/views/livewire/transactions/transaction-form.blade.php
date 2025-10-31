<div>
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <h6 class="mb-0">{{ $transaction ? 'Editar' : 'Nueva' }} Transacción</h6>
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
                            <!-- Financial Product -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Producto Financiero</label>
                                <select class="form-select @error('financial_product_id') is-invalid @enderror"
                                        wire:model="financial_product_id">
                                    <option value="">Seleccionar producto</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error('financial_product_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Transaction Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Transacción</label>
                                <select class="form-select @error('transaction_type') is-invalid @enderror"
                                        wire:model="transaction_type">
                                    <option value="">Seleccionar tipo</option>
                                    <option value="purchase">Compra</option>
                                    <option value="payment">Pago</option>
                                    <option value="transfer">Transferencia</option>
                                    <option value="withdrawal">Retiro</option>
                                    <option value="deposit">Depósito</option>
                                </select>
                                @error('transaction_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lender / Usuario de Tarjeta -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    Prestamista / Usuario de Tarjeta
                                    <i class="fas fa-info-circle text-info ms-1"
                                       title="Selecciona quién realizó esta transacción"></i>
                                </label>
                                <select class="form-select @error('lender_id') is-invalid @enderror"
                                        wire:model="lender_id">
                                    <option value="">Yo mismo (propietario)</option>
                                    @foreach($lenders as $lender)
                                        <option value="{{ $lender->id }}">
                                            {{ $lender->full_name }} ({{ $lender->document_id }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('lender_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Deja en "Yo mismo" si tú realizaste la compra.
                                    <a href="{{ route('lenders.create') }}" wire:navigate class="text-primary">
                                        <i class="fas fa-plus"></i> Agregar prestamista
                                    </a>
                                </small>
                            </div>

                            <!-- Spacer para mantener el grid -->
                            <div class="col-md-6 mb-3"></div>

                            <!-- Amount -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monto</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror"
                                           wire:model="amount" placeholder="0.00">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Transaction Date -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fecha de Transacción</label>
                                <input type="date" class="form-control @error('transaction_date') is-invalid @enderror"
                                       wire:model="transaction_date">
                                @error('transaction_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div class="col-12 mb-3">
                                <label class="form-label">Descripción</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          wire:model="description" rows="3"
                                          placeholder="Detalles de la transacción..."></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Reference Number (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número de Referencia (Opcional)</label>
                                <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                                       wire:model="reference_number" placeholder="REF-12345">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Merchant (Optional) -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Comercio (Opcional)</label>
                                <input type="text" class="form-control @error('merchant') is-invalid @enderror"
                                       wire:model="merchant" placeholder="Nombre del comercio">
                                @error('merchant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Pagar en Cuotas (Solo para compras) -->
                            @if($transaction_type === 'purchase')
                                <div class="col-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="payInInstallments"
                                               wire:model.live="pay_in_installments">
                                        <label class="form-check-label" for="payInInstallments">
                                            <i class="fas fa-calendar-alt me-2"></i>Pagar en cuotas
                                        </label>
                                    </div>
                                </div>

                                @if($pay_in_installments)
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-info d-flex align-items-center" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <div>
                                                <strong>Plan de cuotas automático:</strong> El sistema calculará automáticamente las fechas de pago según el ciclo de facturación de tu tarjeta.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Número de Cuotas</label>
                                        <select class="form-select @error('installments_count') is-invalid @enderror"
                                                wire:model.live="installments_count">
                                            <option value="3">3 cuotas</option>
                                            <option value="6">6 cuotas</option>
                                            <option value="9">9 cuotas</option>
                                            <option value="12">12 cuotas</option>
                                            <option value="18">18 cuotas</option>
                                            <option value="24">24 cuotas</option>
                                            <option value="36">36 cuotas</option>
                                        </select>
                                        @error('installments_count')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if($amount > 0 && $installments_count > 0)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Monto por Cuota</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="text" class="form-control"
                                                       value="{{ number_format($amount / $installments_count, 2) }}"
                                                       readonly>
                                            </div>
                                            <small class="text-muted">
                                                Total: ${{ number_format($amount, 2) }} en {{ $installments_count }} cuotas
                                            </small>
                                        </div>
                                    @endif
                                @endif
                            @endif
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary mb-0" wire:navigate>
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn bg-gradient-success mb-0" wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-save me-2"></i>{{ $transaction ? 'Actualizar' : 'Guardar' }}
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
