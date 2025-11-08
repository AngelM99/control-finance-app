<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta - {{ $reportData['lender']->full_name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; color: #007bff; }
        .header p { font-size: 9px; margin: 5px 0 0 0; color: #666; }
        .lender-info { background: #f8f9fa; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
        .lender-info p { margin: 3px 0; font-size: 10px; }
        .lender-info strong { color: #007bff; }
        .summary-boxes { display: table; width: 100%; margin-bottom: 15px; }
        .summary-box { display: table-cell; width: 25%; text-align: center; padding: 10px; border: 1px solid #ddd; }
        .summary-box .label { font-size: 8px; color: #666; margin-bottom: 5px; }
        .summary-box .value { font-size: 14px; font-weight: bold; }
        .summary-box .value.primary { color: #007bff; }
        .summary-box .value.success { color: #28a745; }
        .summary-box .value.warning { color: #ffc107; }
        .summary-box .value.danger { color: #dc3545; }
        .section-title { font-size: 12px; font-weight: bold; margin: 20px 0 10px 0; color: #007bff; border-bottom: 1px solid #007bff; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #007bff; color: white; padding: 6px; text-align: left; font-size: 9px; }
        table td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 9px; }
        table tr:nth-child(even) { background: #f8f9fa; }
        table tr.highlight { background: #fff3cd; }
        table tr.success-row { background: #d4edda; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; display: inline-block; }
        .badge-success { background: #28a745; color: white; }
        .badge-danger { background: #dc3545; color: white; }
        .badge-warning { background: #ffc107; color: #000; }
        .installment-card { border: 1px solid #ddd; padding: 10px; margin-bottom: 15px; background: #fff; }
        .installment-header { display: table; width: 100%; margin-bottom: 10px; }
        .installment-title { display: table-cell; width: 70%; font-size: 11px; font-weight: bold; }
        .installment-total { display: table-cell; width: 30%; text-align: right; font-size: 11px; color: #ffc107; font-weight: bold; }
        .no-data { text-align: center; padding: 30px; color: #999; font-style: italic; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>ESTADO DE CUENTA</h1>
        <p>Generado el {{ $generatedAt }} por {{ $user->name }}</p>
    </div>

    <!-- Lender Information -->
    <div class="lender-info">
        <p><strong>Prestamista:</strong> {{ $reportData['lender']->full_name }}</p>
        <p><strong>Email:</strong> {{ $reportData['lender']->email }}</p>
        @if($reportData['lender']->phone)
        <p><strong>Teléfono:</strong> {{ $reportData['lender']->phone }}</p>
        @endif
        <p><strong>Período:</strong> {{ ucfirst($reportData['period']['month_name']) }} {{ $reportData['period']['year'] }}
           ({{ $reportData['period']['start_date']->format('d/m/Y') }} - {{ $reportData['period']['end_date']->format('d/m/Y') }})</p>
    </div>

    <!-- Summary Boxes -->
    <div class="summary-boxes">
        <div class="summary-box">
            <div class="label">Total Cuotas del Mes</div>
            <div class="value primary">S/ {{ number_format($reportData['summary']['total_due_amount'] / 100, 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Cuotas Pagadas</div>
            <div class="value success">S/ {{ number_format($reportData['summary']['total_due_paid'] / 100, 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Cuotas Pendientes</div>
            <div class="value danger">S/ {{ number_format($reportData['summary']['total_due_pending'] / 100, 2) }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Productos con Cuotas</div>
            <div class="value">{{ $reportData['summary']['installments_count'] }}</div>
        </div>
    </div>

    <!-- Installments Due Section -->
    @if(count($reportData['installments_due']) > 0)
    <div class="section-title">Cuotas con Vencimiento en el Período</div>
    @foreach($reportData['installments_due'] as $item)
    <div class="installment-card">
        <div class="installment-header">
            <div class="installment-title">
                <strong>{{ $item['installment']->description }}</strong><br>
                <span style="font-size: 8px; color: #666;">{{ $item['installment']->financialProduct->name }}</span>
            </div>
            <div class="installment-total">
                Total: S/ {{ number_format($item['total_due'] / 100, 2) }}
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Cuota</th>
                    <th>Vencimiento</th>
                    <th class="text-right">Monto</th>
                    <th>Fecha de Pago</th>
                    <th class="text-right">Monto Pagado</th>
                    <th style="text-align: center;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item['due_installments'] as $due)
                <tr class="{{ $due['is_paid'] ? 'success-row' : '' }}">
                    <td>{{ $due['number'] }}/{{ $due['total'] }}</td>
                    <td>{{ $due['due_date']->format('d/m/Y') }}</td>
                    <td class="text-right">S/ {{ number_format($due['installment_amount'] / 100, 2) }}</td>
                    <td>
                        @if($due['payment_date'] ?? null)
                            {{ $due['payment_date']->format('d/m/Y') }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">
                        @if($due['payment_amount'] ?? null)
                            S/ {{ number_format($due['payment_amount'] / 100, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($due['is_paid'])
                            <span class="badge badge-success">Pagado</span>
                        @elseif($due['status'] === 'overdue')
                            <span class="badge badge-danger">Vencido</span>
                        @else
                            <span class="badge badge-warning">Pendiente</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach
    @endif

    <!-- No Data Message -->
    @if(count($reportData['installments_due']) === 0)
    <div class="no-data">
        No hay cuotas con vencimiento en este período
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Control Finance - Sistema de Gestión Financiera</p>
        <p>Este documento fue generado automáticamente. Para más información, contacte al administrador.</p>
    </div>
</body>
</html>
