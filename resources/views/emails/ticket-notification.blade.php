<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="x-apple-disable-message-reformatting">
<meta name="format-detection" content="telephone=no,address=no,email=no,date=no">
<title>{{ $action === 'created' ? 'Nuevo ticket' : ($action === 'commented' ? 'Nueva respuesta' : 'Ticket resuelto') }} – Kuali IT Support</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f0;width:100%">

<div style="display:none;font-size:1px;color:#f4f4f0;line-height:1px;max-height:0px;max-width:0px">
  {{ $action === 'created' ? 'Se ha creado un nuevo ticket' : ($action === 'commented' ? 'Nueva respuesta en el ticket' : 'Ticket marcado como resuelto') }} · Kuali IT Support
</div>

<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f4f4f0">
<tr>
<td align="center" style="padding:32px 16px">

  <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width:580px">

    <!-- Header -->
    <tr>
      <td align="center" style="padding-bottom:20px">
        <table cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="background:#2c3e75;border-radius:10px;padding:16px 24px">
            <span style="font-family:Arial,sans-serif;font-size:18px;font-weight:bold;color:#fff">Kuali IT Support</span>
          </td>
        </tr>
        </table>
      </td>
    </tr>

    <!-- Card -->
    <tr>
      <td style="background:#fff;border-radius:12px;padding:28px 32px;border:1px solid #e2e2de">
        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
          <td align="center" style="padding-bottom:16px;border-bottom:1px solid #eee">
            <p style="margin:0 0 4px;font-family:Arial,sans-serif;font-size:11px;font-weight:600;color:#2c3e75;text-transform:uppercase;letter-spacing:0.08em">
              {{ $action === 'created' ? 'Nuevo Ticket' : ($action === 'commented' ? 'Nueva Respuesta' : 'Ticket Resuelto') }}
            </p>
            <h1 style="margin:0;font-family:Arial,sans-serif;font-size:20px;color:#1a1a18">
              {{ $ticket->ticket_number }}
            </h1>
            <p style="margin:6px 0 0;font-family:Arial,sans-serif;font-size:14px;color:#6b6b67">
              {{ $ticket->title }}
            </p>
          </td>
        </tr>

        <tr>
          <td style="padding-top:16px">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td style="padding:8px 0;border-bottom:1px solid #f0f0ec">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                  <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#6b6b67">Prioridad</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;font-weight:600;color:#1a1a18;text-align:right">
                      {{ ucfirst($ticket->priority) }}
                    </td>
                  </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;border-bottom:1px solid #f0f0ec">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                  <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#6b6b67">Estado</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;font-weight:600;color:#1a1a18;text-align:right">
                      {{ ucfirst($ticket->status) }}
                    </td>
                  </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0;border-bottom:1px solid #f0f0ec">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                  <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#6b6b67">Departamento</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;font-weight:600;color:#1a1a18;text-align:right">
                      {{ $ticket->department?->name ?? 'Sin asignar' }}
                    </td>
                  </tr>
                  </table>
                </td>
              </tr>
              <tr>
                <td style="padding:8px 0">
                  <table cellpadding="0" cellspacing="0" border="0" width="100%">
                  <tr>
                    <td style="font-family:Arial,sans-serif;font-size:13px;color:#6b6b67">Categoría</td>
                    <td style="font-family:Arial,sans-serif;font-size:13px;font-weight:600;color:#1a1a18;text-align:right">
                      {{ $ticket->category?->name ?? '—' }} / {{ $ticket->subcategory?->name ?? '—' }}
                    </td>
                  </tr>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        @if ($comment && $comment->comment)
        <tr>
          <td style="padding-top:16px">
            <p style="margin:0 0 8px;font-family:Arial,sans-serif;font-size:12px;font-weight:600;color:#2c3e75;text-transform:uppercase;letter-spacing:0.05em">Mensaje</p>
            <div style="background:#f8f8f6;border-radius:8px;padding:14px 16px;font-family:Arial,sans-serif;font-size:13px;color:#1a1a18;line-height:1.5">
              {{ $comment->comment }}
            </div>
          </td>
        </tr>
        @endif

        @if ($action === 'created' && $ticket->description)
        <tr>
          <td style="padding-top:16px">
            <p style="margin:0 0 8px;font-family:Arial,sans-serif;font-size:12px;font-weight:600;color:#2c3e75;text-transform:uppercase;letter-spacing:0.05em">Descripción</p>
            <div style="background:#f8f8f6;border-radius:8px;padding:14px 16px;font-family:Arial,sans-serif;font-size:13px;color:#1a1a18;line-height:1.5">
              {{ $ticket->description }}
            </div>
          </td>
        </tr>
        @endif

      </table>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="padding:16px 0;text-align:center">
      <p style="margin:0 0 6px;font-family:Arial,sans-serif;font-size:12px;color:#6b6b67">
        {{ $ticket->user?->name }} · {{ $ticket->user?->email }}
      </p>
      <p style="margin:0;font-family:Arial,sans-serif;font-size:12px;color:#9b9b96">
        &copy; {{ date('Y') }} Kuali IT Support
      </p>
    </td>
  </tr>

</table>
</td>
</tr>
</table>

</body>
</html>
