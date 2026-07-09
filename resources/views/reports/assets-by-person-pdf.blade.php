<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte de Activos – {{ $person->last_name }}, {{ $person->first_name }}</title>
<style>
  @page { margin: 0; }
  body {
    font-family: 'DejaVu Sans', sans-serif;
    margin: 0; padding: 0;
    color: #1a1a18;
    background: #fff;
    font-size: 10px;
  }
  .page {
    max-width: 740px;
    margin: 0 auto;
    padding: 40px 48px;
  }
  h1 { font-size: 24px; margin: 0 0 4px; font-weight: 700; color: #2c3e75; }
  .subtitle { font-size: 11px; color: #757681; margin: 0 0 28px; }
  .header-row {
    display: flex; justify-content: space-between; align-items: flex-start;
    padding-bottom: 24px; border-bottom: 1px solid #e2e8f0; margin-bottom: 24px;
  }
  .folio { text-align: right; }
  .folio-label { font-size: 9px; color: #757681; text-transform: uppercase; letter-spacing: 0.8px; }
  .folio-value { font-size: 13px; font-weight: 600; margin-top: 2px; font-family: monospace; }
  .folio-date { font-size: 9px; color: #94a3b8; margin-top: 2px; }

  .person-card {
    display: flex; align-items: center; gap: 20px;
    padding-bottom: 24px; border-bottom: 1px solid #e2e8f0; margin-bottom: 20px;
  }
  .avatar {
    width: 52px; height: 52px; border-radius: 50%;
    background: #eef2ff; color: #2c3e75;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 700; flex-shrink: 0;
  }
  .person-name { font-size: 16px; font-weight: 700; margin: 0 0 4px; }
  .person-info { display: flex; gap: 24px; flex-wrap: wrap; }
  .info-item { min-width: 120px; }
  .info-label { font-size: 8px; color: #757681; text-transform: uppercase; letter-spacing: 0.6px; }
  .info-value { font-size: 11px; color: #454650; margin-top: 2px; }

  .stats-row { display: flex; gap: 16px; padding-bottom: 20px; border-bottom: 1px solid #e2e8f0; margin-bottom: 24px; }
  .stat-card { flex: 1; text-align: center; }
  .stat-value { font-size: 28px; font-weight: 700; }
  .stat-label { font-size: 9px; color: #757681; margin-top: 2px; }
  .stat-value.primary { color: #2c3e75; }
  .stat-value.green { color: #16a34a; }
  .stat-value.amber { color: #ca8a04; }

  .section-title { font-size: 9px; color: #757681; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px; }

  table { width: 100%; border-collapse: collapse; }
  thead th {
    background: #f8fafc; text-align: left;
    font-size: 9px; color: #757681; text-transform: uppercase; letter-spacing: 0.6px;
    padding: 10px 12px; border-bottom: 2px solid #e2e8f0;
  }
  tbody td {
    padding: 12px; border-bottom: 1px solid #f1f5f9;
    font-size: 11px; color: #1a1a18;
  }
  tbody tr:last-child td { border-bottom: none; }
  .asset-name { font-weight: 600; }
  .asset-meta { font-size: 9px; color: #757681; margin-top: 2px; }
  .tag { font-family: monospace; font-size: 10px; }
  .serial { font-family: monospace; font-size: 10px; color: #757681; }
  .date-cell { font-size: 10px; color: #757681; white-space: nowrap; }
  .pill {
    display: inline-block; padding: 3px 10px; border-radius: 99px;
    font-size: 9px; font-weight: 600; text-transform: uppercase;
  }
  .pill-active { background: #dcfce7; color: #166534; }
  .pill-maint { background: #fef9c3; color: #854d0e; }
  .pill-other { background: #f1f5f9; color: #475569; }

  .footer {
    margin-top: 32px; padding-top: 16px; border-top: 1px solid #e2e8f0;
    display: flex; justify-content: space-between;
    font-size: 8px; color: #94a3b8;
  }
  .footer p { margin: 0; max-width: 420px; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header-row">
    <div>
      <h1>Reporte de activos asignados</h1>
      <p class="subtitle">Inventario individual de equipos de cómputo y accesorios de TI</p>
    </div>
  </div>

  <!-- Person info -->
  <div class="person-card">
    <div style="flex:1">
      <div style="display:flex;justify-content:space-between;align-items:flex-start;">
        <p class="person-name">{{ $person->last_name }}, {{ $person->first_name }}</p>
        <div class="folio" style="text-align:right;">
          <div class="folio-label">Folio</div>
          <div class="folio-value">{{ $folio }}</div>
          <div class="folio-date">Generado {{ $today }}</div>
        </div>
      </div>
      <div class="person-info">
        <div class="info-item">
          <div class="info-label">Empresa</div>
          <div class="info-value">{{ $person->company?->name ?? '—' }}</div>
        </div>
        <div class="info-item">
          <div class="info-label">Departamento</div>
          <div class="info-value">{{ $person->department?->name ?? '—' }}</div>
        </div>
        <div class="info-item">
          <div class="info-label">Correo</div>
          <div class="info-value">{{ $person->email ?? '—' }}</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Assets table -->
  <div class="section-title">Detalle de activos</div>
  <table>
    <thead>
      <tr>
        <th>Código</th>
        <th>Nombre</th>
        <th>Marca / Modelo</th>
        <th>Serie</th>
        <th>Asignado</th>
      </tr>
    </thead>
    <tbody>
      @forelse($assets as $a)
      <tr>
        <td><span class="tag">{{ $a->asset_tag }}</span></td>
        <td>
          <div class="asset-name">{{ $a->name }}</div>
          <div class="asset-meta">{{ $a->category?->name ?? '—' }}</div>
        </td>
        <td><div class="asset-meta">{{ $a->brand ?? '—' }} / {{ $a->model ?? '—' }}</div></td>
        <td><span class="serial">{{ $a->serial_number ?? '—' }}</span></td>
        <td class="date-cell">{{ $a->assigned_at?->format('d M Y') ?? $a->purchase_date?->format('d M Y') ?? '—' }}</td>
      </tr>
      @empty
      <tr>
        <td colspan="5" style="text-align:center;padding:32px;color:#757681;">Sin activos asignados.</td>
      </tr>
      @endforelse
    </tbody>
  </table>

  <!-- Footer -->
  <div class="footer">
    <p>Este documento certifica la asignación de los activos listados bajo la responsabilidad del colaborador indicado, según política interna de uso de equipo de TI.</p>
    <span style="flex-shrink:0;">Emitido por: Dept. de TI</span>
  </div>

</div>
</body>
</html>
