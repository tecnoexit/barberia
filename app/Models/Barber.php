<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barber extends Model
{
    protected $fillable = ['user_id', 'name', 'phone', 'photo', 'bio', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function schedules() { return $this->hasMany(BarberSchedule::class); }
    public function appointments() { return $this->hasMany(Appointment::class); }
}
