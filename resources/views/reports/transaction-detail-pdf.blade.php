<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalle de Transacción</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header h1 { font-size: 20px; margin: 0; color: #007bff; }
        .header p { font-size: 10px; margin: 5px 0 0 0; color: #666; }
        .info-section { margin-bottom: 15px; }
        .info-grid { display: table; width: 100%; margin-bottom: 15px; }
        .info-row { display: table-row; }
        .info-cell { display: table-cell; padding: 8px; background: #f8f9fa; border: 1px solid #ddd; width: 50%; }
        .info-cell strong { color: #007bff; }
        .stats-grid { display: table; width: 100%; margin-bottom: 20px; }
        .stats-row { display: table-row; }
        .stats-cell { display: table-cell; padding: 10px; text-align: center; background: #e7f1ff; border: 1px solid #007bff; width: 25%; }
        .stats-cell h3 { font-size: 12px; margin: 0; color: #007bff; }
        .stats-cell .value { font-size: 16px; font-weight: bold; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th { background: #007bff; color: white; padding: 8px; text-align: left; font-size: 10px; }
        table td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10px; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-success { background: #28a745; color: white; }
        .badge-warning { background: #ffc107; color: black; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .text-success { color: #28a745; font-weight: bold; }
        .text-warning { color: #ffc107; }
        .text-danger { color: #dc3545; }
        .row-paid { background: #d4edda; }
        .row-partial { background: #fff3cd; }
        .row-overdue { background: #f8d7da; }
        .footer { text-align: center; font-size: 9px; color: #999; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>DETALLE DE TRANSACCIÓN</h1>
        <p>Generado el {{ $generatedAt }}</p>
    </div>

    <!-- Transaction Info -->
    <div class="info-section">
        <h3 style="color: #007bff; font-size: 14px; margin-bottom: 10px;">Información de la Transacción</h3>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <strong>Prestamista:</strong><br>
                    {{ $transaction->lender->full_name ?? 'N/A' }}<br>
                    <small style="color: #666;">{{ $transaction->lender->email ?? '' }}</small>
                </div>
                <div class="info-cell">
                    <strong>Tarjeta de Crédito:</strong><br>
                    {{ $transaction->financialProduct->name ?? 'N/A' }}<br>
                    <small style="color: #666;">{{ $transaction->financialProduct->institution ?? '' }}</small>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <strong>Descripción:</strong><br>
                    {{ $transaction->description }}
                </div>
                <div class="info-cell">
                    <strong>Fecha de Transacción:</strong><br>
                    {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <strong>Monto Total:</strong><br>
                    <span style="font-size: 16px; color: #007bff; font-weight: bold;">S/ {{ number_format($transaction->amount / 100, 2) }}</span>
                </div>
                <div class="info-cell">
                    <strong>Número de Cuotas:</strong><br>
                    <span style="font-size: 16px; font-weight: bold;">{{ $transaction->installment_count }}</span>
                </div>
            </div>
        </div>
    </div>

    @if($installment)
    <!-- Installment Summary -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell">
                <h3>Total Cuotas</h3>
                <div class="value">{{ $installment->installment_count }}</div>
            </div>
            <div class="stats-cell">
                <h3>Monto por Cuota</h3>
                <div class="value">S/ {{ number_format($installment->installment_amount / 100, 2) }}</div>
            </div>
            <div class="stats-cell">
                <h3>Total Pagado</h3>
                <div class="value text-success">S/ {{ number_format($installment->total_paid / 100, 2) }}</div>
            </div>
            <div class="stats-cell">
                <h3>Saldo Pendiente</h3>
                <div class="value text-warning">S/ {{ number_format(($installment->total_amount - $installment->total_paid) / 100, 2) }}</div>
            </div>
        </div>
    </div>

    <!-- Cuota by Cuota Schedule -->
    <h3 style="color: #007bff; font-size: 14px; margin-bottom: 10px; margin-top: 20px;">Cronograma Detallado de Cuotas</h3>
    <table>
        <thead>
            <tr>
                <th>Cuota</th>
                <th>Fecha Programada</th>
                <th>Monto Cuota</th>
                <th>Monto Pagado</th>
                <th>Fecha Pago</th>
                <th>Saldo Restante</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedule as $cuota)
            <tr class="{{ $cuota['status'] === 'paid' ? 'row-paid' : ($cuota['status'] === 'overdue' ? 'row-overdue' : ($cuota['status'] === 'partial' ? 'row-partial' : '')) }}">
                <td><strong>{{ $cuota['number'] }}/{{ $cuota['total'] }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($cuota['scheduled_date'])->format('d/m/Y') }}</td>
                <td>S/ {{ number_format($cuota['installment_amount'] / 100, 2) }}</td>
                <td class="{{ $cuota['paid_amount'] > 0 ? 'text-success' : '' }}">
                    S/ {{ number_format($cuota['paid_amount'] / 100, 2) }}
                </td>
                <td>{{ $cuota['payment_date'] ? \Carbon\Carbon::parse($cuota['payment_date'])->format('d/m/Y') : '-' }}</td>
                <td>S/ {{ number_format($cuota['remaining_balance'] / 100, 2) }}</td>
                <td>
                    @if($cuota['status'] === 'paid')
                        <span class="badge badge-success">Pagado</span>
                    @elseif($cuota['status'] === 'partial')
                        <span class="badge badge-warning">Parcial</span>
                    @elseif($cuota['status'] === 'overdue')
                        <span class="badge badge-danger">Vencido</span>
                    @else
                        <span class="badge badge-secondary">Pendiente</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; text-align: center; margin-top: 20px;">
        <strong>No se encontró plan de cuotas asociado a esta transacción</strong>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Control Finance - Sistema de Gestión Financiera</p>
        <p>Reporte generado el {{ $generatedAt }}</p>
    </div>
</body>
</html>
