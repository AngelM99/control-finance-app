<div>
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Prestamistas / Usuarios de Tarjeta</h6>
                        <a href="{{ route('lenders.create') }}" class="btn btn-sm bg-gradient-success mb-0" wire:navigate>
                            <i class="fas fa-plus me-2"></i>Nuevo Prestamista
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    @if($lenders->count() > 0)
                        <div class="table-responsive p-3" wire:ignore.self>
                            <table id="lendersTable" class="table table-hover align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nombre Completo</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">DNI / Documento</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contacto</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Transacciones</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estado</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lenders as $lender)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-3 py-1">
                                                    <div class="icon icon-shape icon-sm bg-gradient-primary shadow text-center border-radius-md me-2 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-user text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $lender->full_name }}</h6>
                                                        @if($lender->notes)
                                                            <p class="text-xs text-secondary mb-0">{{ Str::limit($lender->notes, 30) }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $lender->document_id }}</p>
                                            </td>
                                            <td>
                                                @if($lender->phone)
                                                    <p class="text-xs mb-0">
                                                        <i class="fas fa-phone me-1"></i>{{ $lender->phone }}
                                                    </p>
                                                @endif
                                                @if($lender->email)
                                                    <p class="text-xs mb-0">
                                                        <i class="fas fa-envelope me-1"></i>{{ $lender->email }}
                                                    </p>
                                                @endif
                                                @if(!$lender->phone && !$lender->email)
                                                    <span class="text-xs text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm bg-gradient-info">
                                                    {{ $lender->transactions_count }} transacciones
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <button wire:click="toggleStatus({{ $lender->id }})"
                                                        class="badge badge-sm bg-gradient-{{ $lender->is_active ? 'success' : 'secondary' }} border-0"
                                                        style="cursor: pointer;">
                                                    {{ $lender->is_active ? 'Activo' : 'Inactivo' }}
                                                </button>
                                            </td>
                                            <td class="align-middle text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="{{ route('lenders.edit', $lender) }}"
                                                       class="btn btn-sm btn-outline-primary mb-0"
                                                       wire:navigate
                                                       title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button wire:click="deleteLender({{ $lender->id }})"
                                                            wire:confirm="¿Está seguro de eliminar este prestamista?"
                                                            class="btn btn-sm btn-outline-danger mb-0"
                                                            title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-sm text-secondary mb-3">No hay prestamistas registrados</p>
                            <a href="{{ route('lenders.create') }}" class="btn bg-gradient-success" wire:navigate>
                                <i class="fas fa-plus me-2"></i>Registrar Primer Prestamista
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($lenders->count() > 0)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                initLendersTable();
            });

            document.addEventListener('livewire:navigated', function() {
                initLendersTable();
            });

            function initLendersTable() {
                if ($.fn.DataTable.isDataTable('#lendersTable')) {
                    $('#lendersTable').DataTable().destroy();
                }

                $('#lendersTable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                    },
                    pageLength: 25,
                    order: [[0, 'asc']], // Ordenar por nombre
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel me-2"></i>Excel',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf me-2"></i>PDF',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print me-2"></i>Imprimir',
                            className: 'dt-button',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        }
                    ],
                    columnDefs: [
                        { orderable: false, targets: [5] }
                    ]
                });
            }
        </script>
        @endpush
    @endif
</div>
