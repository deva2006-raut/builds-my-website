<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Source extends Model
{
    protected $fillable = ['site_id', 'type', 'origin', 'status', 'text', 'error'];

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
