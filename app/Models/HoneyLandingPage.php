<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoneyLandingPage extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    
    protected $casts = [
        'content' => 'array'
    ];

    /**
     * Get a specific section from content
     */
    public function getSection($section)
    {
        return $this->content[$section] ?? null;
    }

    /**
     * Check if page is active
     */
    public function isActive()
    {
        return $this->status == 1;
    }
}
