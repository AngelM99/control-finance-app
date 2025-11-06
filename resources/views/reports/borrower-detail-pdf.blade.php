<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalle de Prestamista - {{ $borrower->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; color: #007bff; }
        .header p { font-size: 10px; margin: 5px 0 0 0; color: #666; }
        .section { margin-bottom: 15px; }
        .section-title { font-size: 14px; font-weight: bold; color: #007bff; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 10px; }
        .info-grid { display: table; width: 100%; margin-bottom: 10px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 5px; width: 50%; }
        .info-cell strong { font-weight: bold; }
        .stats-box { background: #f8f9fa; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 10px; }
        .stats-box h3 { font-size: 16px; margin: 0; color: #007bff; }
        .stats-box p { font-size: 10px; margin: 3px 0 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        table th { background: #007bff; color: white; padding: 8px; text-align: left; font-size: 10px; }
        table td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10px; }
        table tr:nth-child(even) { background: #f8f9fa; }
        .text-success { color: #28a745; }
        .text-warning { color: #ffc107; }
        .text-danger { color: #dc3545; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-info { background: #17a2b8; color: white; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>DETALLE DE PRESTAMISTA</h1>
        <p>{{ $borrower->name }} - Generado el {{ $generatedAt }}</p>
    </div>

    <!-- Borrower Info -->
    <div class="section">
        <div class="section-title">Información del Prestamista</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell"><strong>Nombre:</strong> {{ $borrower->name }}</div>
                <div class="info-cell"><strong>Email:</strong> {{ $borrower->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-cell"><strong>Teléfono:</strong> {{ $borrower->phone ?? 'N/A' }}</div>
                <div class="info-cell"><strong>DNI:</strong> {{ $borrower->dni ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Stats Summary -->
    <div class="section">
        <div class="section-title">Resumen Financiero</div>
        <table>
            <tr>
                <th>Total Prestado</th>
                <th>Total Pagado</th>
                <th>Saldo Pendiente</th>
                <th>Progreso</th>
            </tr>
            <tr>
                <td>S/ {{ number_format($stats['total_lent'] / 100, 2) }}</td>
                <td class="text-success">S/ {{ number_format($stats['total_paid'] / 100, 2) }}</td>
                <td class="text-warning">S/ {{ number_format($stats['remaining_debt'] / 100, 2) }}</td>
                <td>{{ number_format($stats['progress_percentage'], 1) }}%</td>
            </tr>
        </table>
    </div>

    <!-- Transactions -->
    <div class="section">
        <div class="section-title">Compras Realizadas</div>
        @if(count($transactions) > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Monto</th>
                    <th>Cuotas</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $transaction)
                <tr>
                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                    <td>{{ $transaction->financialProduct?->name ?? 'N/A' }}</td>
                    <td>{{ $transaction->description ?? '-' }}</td>
                    <td>S/ {{ number_format($transaction->amount / 100, 2) }}</td>
                    <td>{{ $transaction->installment_count ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay compras registradas</p>
        @endif
    </div>

    <!-- Installments -->
    <div class="section">
        <div class="section-title">Planes de Cuotas</div>
        @if(count($installments) > 0)
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Progreso</th>
                    <th>Total</th>
                    <th>Pagado</th>
                    <th>Restante</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($installments as $installment)
                <tr>
                    <td>{{ $installment->financialProduct?->name ?? 'N/A' }}</td>
                    <td>{{ $installment->current_installment }} / {{ $installment->installment_count }}</td>
                    <td>S/ {{ number_format($installment->total_amount / 100, 2) }}</td>
                    <td class="text-success">S/ {{ number_format(($installment->total_paid ?? 0) / 100, 2) }}</td>
                    <td class="text-warning">S/ {{ number_format($installment->remaining_amount / 100, 2) }}</td>
                    <td>
                        @if($installment->status === 'completed')
                            <span class="badge badge-success">Completado</span>
                        @else
                            <span class="badge badge-info">Activo</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay planes de cuotas</p>
        @endif
    </div>

    <!-- Payment History -->
    <div class="section">
        <div class="section-title">Historial de Pagos</div>
        @if(count($payments) > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha Pago</th>
                    <th>Producto</th>
                    <th>Monto Pagado</th>
                    <th>Saldo Después</th>
                    <th>Registrado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($payment['payment_date'])->format('d/m/Y') }}</td>
                    <td>{{ $payment['installment']->financialProduct?->name ?? 'N/A' }}</td>
                    <td class="text-success">S/ {{ number_format($payment['amount_dollars'], 2) }}</td>
                    <td class="text-warning">S/ {{ number_format($payment['remaining_after'], 2) }}</td>
                    <td>{{ $payment['registered_at'] ? \Carbon\Carbon::parse($payment['registered_at'])->format('d/m/Y H:i') : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay pagos registrados</p>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Control Finance - Sistema de Gestión Financiera</p>
        <p>Documento generado el {{ $generatedAt }}</p>
    </div>
</body>
</html>
