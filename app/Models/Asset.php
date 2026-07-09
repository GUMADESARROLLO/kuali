<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Asset extends Model
{
    protected $fillable = [
        'asset_tag', 'name', 'company_id', 'asset_category_id', 'parent_asset_id',
        'brand', 'model', 'serial_number', 'status', 'person_id', 'assigned_at', 'location',
        'purchase_date', 'purchase_cost', 'warranty_expiry', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'warranty_expiry' => 'date',
            'assigned_at' => 'datetime',
            'purchase_cost' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function category(): BelongsTo { return $this->belongsTo(AssetCategory::class, 'asset_category_id'); }
    public function parent(): BelongsTo { return $this->belongsTo(Asset::class, 'parent_asset_id'); }
    public function children(): HasMany { return $this->hasMany(Asset::class, 'parent_asset_id'); }
    public function assignedPerson(): BelongsTo { return $this->belongsTo(Person::class, 'person_id'); }
    public function maintenance(): HasMany { return $this->hasMany(AssetMaintenance::class); }
    public function softwareLicenses(): BelongsToMany
    {
        return $this->belongsToMany(SoftwareLicense::class, 'asset_software')
            ->withPivot('installed_at', 'uninstalled_at');
    }
}
