<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaRule extends Model
{
    protected $fillable = [
        'name', 'description', 'calendar_id', 'conditions',
        'first_response_hours', 'update_hours', 'solution_hours',
        'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'conditions' => 'array',
            'is_active' => 'boolean',
            'first_response_hours' => 'integer',
            'update_hours' => 'integer',
            'solution_hours' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function calendar(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Calendar::class);
    }

    public function tickets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Ticket::class, 'sla_rule_id');
    }
}
