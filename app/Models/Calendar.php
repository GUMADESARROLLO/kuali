<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected $fillable = ['name', 'description', 'is_active', 'hours'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'hours' => 'array',
        ];
    }

    public function slaRules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SlaRule::class);
    }
}
