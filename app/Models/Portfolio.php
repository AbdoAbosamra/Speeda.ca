<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $table = 'portfolios';

    protected $fillable = [
        'service_provider_id',
        'title',
        'description',
        'image',
        'video_url',
        'project_date',
        'project_cost',
        'sort_order',
        'is_featured',
    ];

    protected $casts = [
        'project_date' => 'date',
        'project_cost' => 'decimal:2',
        'sort_order' => 'integer',
        'is_featured' => 'boolean',
    ];

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class, 'service_provider_id');
    }
}
