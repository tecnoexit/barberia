<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name', 'description', 'price', 'duration_minutes', 'image', 'is_active'];
    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean'];
    public function appointments() { return $this->hasMany(Appointment::class); }
}
