<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'mode',
        'status',
        'message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
