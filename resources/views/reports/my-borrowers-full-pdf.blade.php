<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mis Prestamistas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .header h1 { font-size: 20px; margin: 0; color: #007bff; }
        .header p { font-size: 10px; margin: 5px 0 0 0; color: #666; }
        .stats-grid { display: table; width: 100%; margin-bottom: 20px; }
        .stats-row { display: table-row; }
        .stats-cell { display: table-cell; padding: 10px; text-align: center; background: #f8f9fa; border: 1px solid #ddd; width: 25%; }
        .stats-cell h3 { font-size: 14px; margin: 0; color: #007bff; }
        .stats-cell p { font-size: 9px; margin: 5px 0 0 0; color: #666; }
        .stats-cell .value { font-size: 16px; font-weight: bold; margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th { background: #007bff; color: white; padding: 8px; text-align: left; font-size: 10px; }
        table td { padding: 6px; border-bottom: 1px solid #ddd; font-size: 10px; }
        table tr:nth-child(even) { background: #f8f9fa; }
        .text-success { color: #28a745; }
        .text-warning { color: #ffc107; }
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
        <h1>REPORTE DE MIS PRESTAMISTAS</h1>
        <p>Propietario: {{ $user->name }} - Generado el {{ $generatedAt }}</p>
    </div>

    <!-- Global Stats -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stats-cell">
                <h3>Prestamistas</h3>
                <div class="value">{{ $globalStats['total_borrowers'] }}</div>
                <p>Total</p>
            </div>
            <div class="stats-cell">
                <h3>Total Prestado</h3>
                <div class="value">S/ {{ number_format($globalStats['total_lent'] / 100, 2) }}</div>
                <p>De mis productos</p>
            </div>
            <div class="stats-cell">
                <h3>Total Recuperado</h3>
                <div class="value text-success">S/ {{ number_format($globalStats['total_paid'] / 100, 2) }}</div>
                <p>Pagado</p>
            </div>
            <div class="stats-cell">
                <h3>Por Cobrar</h3>
                <div class="value text-warning">S/ {{ number_format($globalStats['total_debt'] / 100, 2) }}</div>
                <p>Pendiente</p>
            </div>
        </div>
    </div>

    <!-- Borrowers Table -->
    <table>
        <thead>
            <tr>
                <th>Prestamista</th>
                <th>Total Prestado</th>
                <th>Total Pagado</th>
                <th>Saldo Pendiente</th>
                <th>Cuotas Activas</th>
                <th>Cuotas Completadas</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($borrowers as $borrower)
            <tr>
                <td>
                    <strong>{{ $borrower['name'] }}</strong><br>
                    <small style="color: #666;">{{ $borrower['email'] }}</small>
                </td>
                <td>S/ {{ number_format($borrower['total_lent'] / 100, 2) }}</td>
                <td class="text-success">S/ {{ number_format($borrower['total_paid'] / 100, 2) }}</td>
                <td class="text-warning">S/ {{ number_format($borrower['remaining_debt'] / 100, 2) }}</td>
                <td><span class="badge badge-info">{{ $borrower['active_installments_count'] }}</span></td>
                <td><span class="badge badge-success">{{ $borrower['completed_installments_count'] }}</span></td>
                <td>
                    @if($borrower['status'] === 'active')
                        <span class="badge badge-warning">Activo</span>
                    @else
                        <span class="badge badge-success">Completado</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        <p>Control Finance - Sistema de Gestión Financiera</p>
        <p>Reporte generado el {{ $generatedAt }}</p>
    </div>
</body>
</html>
