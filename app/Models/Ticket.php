<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_OPEN = 'abierto';
    public const STATUS_IN_PROGRESS = 'en_proceso';
    public const STATUS_ON_HOLD = 'en_espera';
    public const STATUS_RESOLVED = 'resuelto';
    public const STATUS_CLOSED = 'cerrado';
    public const STATUS_CANCELED = 'cancelado';

    public const PRIORITY_LOW = 'baja';
    public const PRIORITY_MEDIUM = 'media';
    public const PRIORITY_HIGH = 'alta';
    public const PRIORITY_URGENT = 'urgente';

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'user_id',
        'department_id',
        'category_id',
        'subcategory_id',
        'priority',
        'status',
        'assigned_to',
        'assigned_area',
        'due_date',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->latest();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class)->latest();
    }

    public function rating(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TicketRating::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_ON_HOLD,
        ], true);
    }
}
