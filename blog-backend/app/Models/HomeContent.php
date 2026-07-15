<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeContent extends Model
{
    protected $fillable = [
        'badge_es', 'badge_en',
        'title_es', 'title_en',
        'subtitle_es', 'subtitle_en',
        'primary_label_es', 'primary_label_en',
        'primary_url_es', 'primary_url_en',
        'secondary_label_es', 'secondary_label_en',
        'secondary_url_es', 'secondary_url_en',
        'image1', 'image2', 'image3', 'image4',
        'image1_alt_es', 'image1_alt_en',
        'image2_alt_es', 'image2_alt_en',
        'image3_alt_es', 'image3_alt_en',
        'image4_alt_es', 'image4_alt_en',
    ];
}
