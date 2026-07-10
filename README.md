# Kuali — Helpdesk IT &amp; Gestión de Activos

Sistema interno de mesa de ayuda (helpdesk) y control de inventario para departamentos de TI. Permite gestionar tickets de soporte, acuerdos de nivel de servicio (SLA) y activos tecnológicos asignados a colaboradores.

## Stack técnico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13 (PHP 8.4) |
| Frontend | Vue 3 + Inertia.js + Tailwind CSS 4 |
| Base de datos | MySQL |
| Auth / Roles | Laravel Breeze + Spatie Permission |
| Notificaciones | Correo electrónico (SMTP) + Mailtrap (sandbox) |
| Reportes | DomPDF (PDF) + Maatwebsite/Excel (XLSX) |
| Almacenamiento | S3-compatible (MinIO / AWS S3) |

## Módulos principales

### Helpdesk (mesa de ayuda)

- **Tickets** — Creación, asignación, comentarios, resolución y reclasificación.
- **Roles** — `usuario`, `agente_it`, `admin_it`. Cada rol ve un dashboard distinto.
- **SLA** — Acuerdos de nivel de servicio configurables con calendario laboral (horas hábiles). Cálculo automático de fechas límite para primera respuesta, actualización y solución.
- **Notificaciones** — Correo al crear/responder/asignar/resolver un ticket.
- **Reportes** — Métricas de tickets por departamento, agente y tipo. Exportación a Excel.

### Inventario de activos

- **Empresas / Departamentos / Personas** — Estructura jerárquica multi-empresa.
- **Activos** — PCs, laptops, monitores, teléfonos, impresoras, etc. Cada activo tiene código único, marca, modelo, serie, ubicación y puede agruparse jerárquicamente (ej. PC → monitor + teclado + mouse).
- **Mantenimiento** — Historial de reparaciones y reemplazos por activo (tipo, componente, costo, fecha).
- **Licencias de software** — Registro de licencias con tipo de pago (único, mensual, anual), asientos totales/usados y asignación a activos.
- **Reporte de activos por persona** — PDF imprimible con listado de equipos asignados.

## Roles del sistema

| Rol | Acceso |
|-----|--------|
| `admin_it` | Dashboard admin, todos los tickets, CRUD de catálogos, SLA, reportes e inventario completo. |
| `agente_it` | Dashboard admin, gestión de tickets asignados, cambio de estado/prioridad. |
| `usuario` | Dashboard personal, crear tickets en su departamento, ver estado de sus tickets. |

## Instalación rápida

```bash
git clone <repo-url>
cd kuali
composer install
npm install
cp .env.example .env
php artisan key:generate

# Configurar .env con credenciales de BD, Mail y S3
php artisan migrate:fresh --seed
npm run build
```

## Comandos útiles

```bash
php artisan serve                     # Servidor de desarrollo
npm run dev                           # Hot reload frontend
php artisan sla:check                 # Verificar tickets vencidos (SLA)
php artisan schedule:run              # Ejecutar tareas programadas
```

## Estructura del proyecto

```
app/
├── Http/Controllers/Admin/   # Controladores del panel admin
├── Mail/                     # Notificaciones por correo
├── Models/                   # Modelos Eloquent
└── Services/                 # Lógica de negocio (SLA, tickets)
database/
├── migrations/
└── seeders/                  # Datos de demostración
resources/
├── js/
│   ├── Components/           # Componentes Vue reutilizables
│   ├── Layouts/              # Plantillas (AppLayout, GuestLayout)
│   └── Pages/Admin/          # Páginas del panel admin
└── views/                    # Plantillas Blade (PDF, emails)
```

## Licencia

Proyecto privado — uso interno.
