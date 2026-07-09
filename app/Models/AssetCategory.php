<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AssetCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        static::saving(function ($cat) {
            if (!$cat->slug) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function assets(): HasMany { return $this->hasMany(Asset::class); }
}
