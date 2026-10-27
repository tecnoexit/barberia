<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['barber_id', 'service_id', 'client_name', 'client_phone', 'date', 'start_time', 'end_time', 'status'];
    protected $casts = ['date' => 'date'];
    public function barber() { return $this->belongsTo(Barber::class); }
    public function service() { return $this->belongsTo(Service::class); }
}
