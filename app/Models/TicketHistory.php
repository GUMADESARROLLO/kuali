<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    use HasFactory;

    public const ACTION_CREATED = 'creado';
    public const ACTION_ASSIGNED = 'asignado';
    public const ACTION_REASSIGNED = 'reasignado';
    public const ACTION_STATUS_CHANGED = 'cambio_estado';
    public const ACTION_COMMENTED = 'comentario';
    public const ACTION_ATTACHMENT = 'adjunto';
    public const ACTION_RECLASSIFIED = 'reclasificado';
    public const ACTION_CLOSED = 'cerrado';
    public const ACTION_RESOLVED = 'resuelto';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'description',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
