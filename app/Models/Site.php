<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'slug', 'business_name', 'city', 'status', 'ai_model', 'profile', 'conflicts', 'published_at',
    ];

    protected $casts = [
        'profile' => 'array',
        'conflicts' => 'array',
        'published_at' => 'datetime',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class)->orderBy('position');
    }

    public function sources(): HasMany
    {
        return $this->hasMany(Source::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function publicUrl(): string
    {
        return route('public.site', $this->slug);
    }

    /**
     * Two businesses with the same name must not collide: suffix the city,
     * then a counter, and keep trying until the slug is free.
     */
    public static function uniqueSlug(string $name, ?string $city = null, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug(trim($name . ' ' . (string) $city)) ?: 'site';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
