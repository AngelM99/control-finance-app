<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Transacciones</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; color: #007bff; }
        .header p { font-size: 9px; margin: 5px 0 0 0; color: #666; }
        .filters { background: #f8f9fa; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd; }
        .filters p { margin: 3px 0; font-size: 10px; }
        .filters strong { color: #007bff; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #007bff; color: white; padding: 6px; text-align: left; font-size: 9px; }
        table td { padding: 5px; border-bottom: 1px solid #ddd; font-size: 9px; }
        table tr:nth-child(even) { background: #f8f9fa; }
        .badge { padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-info { background: #17a2b8; color: white; }
        .badge-secondary { background: #6c757d; color: white; }
        .footer { text-align: center; font-size: 8px; color: #999; margin-top: 20px; padding-top: 10px; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>REPORTE DE TRANSACCIONES</h1>
        <p>Propietario: {{ $user->name }} - Generado el {{ $generatedAt }}</p>
    </div>

    <!-- Filters Applied -->
    <div class="filters">
        <p><strong>Filtros Aplicados:</strong></p>
        <p>Prestamista: {{ $filters['borrower'] }}</p>
        <p>Desde: {{ $filters['dateFrom'] }} | Hasta: {{ $filters['dateTo'] }}</p>
    </div>

    <!-- Transactions Table -->
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Prestamista</th>
                <th>Tarjeta</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Cuotas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $transaction)
            <tr>
                <td>{{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d/m/Y') }}</td>
                <td>
                    <strong>{{ $transaction->lender->full_name ?? 'N/A' }}</strong><br>
                    <small style="color: #666;">{{ $transaction->lender->email ?? '' }}</small>
                </td>
                <td>
                    <strong>{{ $transaction->financialProduct->name ?? 'N/A' }}</strong><br>
                    <small style="color: #666;">{{ $transaction->financialProduct->institution ?? '' }}</small>
                </td>
                <td>{{ \Illuminate\Support\Str::limit($transaction->description, 60) }}</td>
                <td><strong>S/ {{ number_format($transaction->amount / 100, 2) }}</strong></td>
                <td>
                    @if($transaction->installment_count > 0)
                        <span class="badge badge-info">{{ $transaction->installment_count }} cuotas</span>
                    @else
                        <span class="badge badge-secondary">Sin cuotas</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px;">No hay transacciones para mostrar</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary -->
    <div style="margin-top: 20px; background: #e7f1ff; padding: 10px; border: 1px solid #007bff;">
        <p style="margin: 3px 0;"><strong>Total de Transacciones:</strong> {{ $transactions->count() }}</p>
        <p style="margin: 3px 0;"><strong>Monto Total:</strong> S/ {{ number_format($transactions->sum('amount') / 100, 2) }}</p>
        <p style="margin: 3px 0;"><strong>Transacciones con Cuotas:</strong> {{ $transactions->where('installment_count', '>', 0)->count() }}</p>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Control Finance - Sistema de Gestión Financiera</p>
        <p>Reporte generado el {{ $generatedAt }}</p>
    </div>
</body>
</html>
