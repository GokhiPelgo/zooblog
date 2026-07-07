<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'prismic_id',
        'uid',
        'lang',
        'title',
        'description',
        'image_url',
        'image_alt',
        'tags',
        'content',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
