<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SoftwareLicense extends Model
{
    protected $fillable = [
        'company_id', 'name', 'license_key', 'vendor', 'seats_total',
        'seats_used', 'recurring', 'billing_period',
        'purchase_date', 'expiry_date', 'cost', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expiry_date' => 'date',
            'cost' => 'decimal:2',
            'seats_total' => 'integer',
            'seats_used' => 'integer',
            'recurring' => 'boolean',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function assets(): BelongsToMany { return $this->belongsToMany(Asset::class, 'asset_software')->withPivot('installed_at', 'uninstalled_at'); }
}
