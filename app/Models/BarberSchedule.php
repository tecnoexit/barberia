<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarberSchedule extends Model
{
    protected $fillable = ['barber_id', 'day_of_week', 'start_time', 'end_time', 'break_start', 'break_end'];
    public function barber() { return $this->belongsTo(Barber::class); }
}
