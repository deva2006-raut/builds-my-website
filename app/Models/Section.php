<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Section extends Model
{
    protected $fillable = ['site_id', 'key', 'position', 'content'];

    protected $casts = [
        'content' => 'array',
    ];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function heading(): string
    {
        return (string) ($this->content['heading'] ?? '');
    }

    public function body(): string
    {
        return (string) ($this->content['body'] ?? '');
    }

    public function items(): array
    {
        return (array) ($this->content['items'] ?? []);
    }
}
