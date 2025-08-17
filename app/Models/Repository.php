<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Repository extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'url',
        'local_path',
        'branch',
        'last_pulled_at'
    ];
    
    protected $attributes = [
        'branch' => 'main',
    ];

    protected $casts = [
        'last_pulled_at' => 'datetime',
    ];
    
    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    
    /**
     * Boot function to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($repository) {
            if (!$repository->slug) {
                $slug = Str::slug($repository->name);
                $originalSlug = $slug;
                $count = 1;
                
                while (static::where('slug', $slug)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                
                $repository->slug = $slug;
            }
        });
        
        static::updating(function ($repository) {
            if ($repository->isDirty('name') && !$repository->isDirty('slug')) {
                $slug = Str::slug($repository->name);
                $originalSlug = $slug;
                $count = 1;
                
                while (static::where('slug', $slug)->where('id', '!=', $repository->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                
                $repository->slug = $slug;
            }
        });
    }
}
