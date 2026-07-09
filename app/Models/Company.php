<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Company extends Model
{
    protected $fillable = ['name', 'slug', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function ($company) {
            if (!$company->slug) {
                $company->slug = Str::slug($company->name);
            }
        });
    }

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function assets(): HasMany { return $this->hasMany(Asset::class); }
    public function softwareLicenses(): HasMany { return $this->hasMany(SoftwareLicense::class); }
}
