<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetMaintenance extends Model
{
    protected $table = 'asset_maintenance';
    protected $fillable = [
        'asset_id', 'maintenance_type', 'component', 'description',
        'cost', 'performed_by', 'performed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'performed_at' => 'datetime',
            'cost' => 'decimal:2',
        ];
    }

    public function asset(): BelongsTo { return $this->belongsTo(Asset::class); }
}
