<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function home() { return view('booking.home', ['services' => Service::where('is_active', true)->get(), 'barbers' => Barber::where('is_active', true)->get()]); }

    public function slots(Request $request)
    {
        $data = $request->validate(['barber_id' => 'required|exists:barbers,id', 'service_id' => 'required|exists:services,id', 'date' => 'required|date']);
        $date = Carbon::parse($data['date']); $service = Service::findOrFail($data['service_id']);
        $schedule = Barber::findOrFail($data['barber_id'])->schedules()->where('day_of_week', $date->dayOfWeek)->first();
        if (!$schedule) return response()->json([]);
        $busy = Appointment::where('barber_id', $data['barber_id'])->whereDate('date', $date)->where('status', '!=', 'canceled')->get();
        $slots = [];
        $cursor = Carbon::parse($date->toDateString().' '.$schedule->start_time); $close = Carbon::parse($date->toDateString().' '.$schedule->end_time);
        while ($cursor->copy()->addMinutes($service->duration_minutes)->lte($close)) {
            $end = $cursor->copy()->addMinutes($service->duration_minutes);
            $inBreak = $schedule->break_start && $cursor->format('H:i:s') < $schedule->break_end && $end->format('H:i:s') > $schedule->break_start;
            $occupied = $busy->contains(fn ($a) => $cursor->format('H:i:s') < $a->end_time && $end->format('H:i:s') > $a->start_time);
            if (!$inBreak && !$occupied && $cursor->isFuture()) $slots[] = ['start' => $cursor->format('H:i'), 'end' => $end->format('H:i')];
            $cursor->addMinutes(15);
        }
        return response()->json($slots);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['barber_id'=>'required|exists:barbers,id','service_id'=>'required|exists:services,id','client_name'=>'required|string|max:120','client_phone'=>'required|string|max:40','date'=>'required|date|after_or_equal:today','start_time'=>'required|date_format:H:i']);
        $service = Service::findOrFail($data['service_id']); $start = Carbon::parse($data['date'].' '.$data['start_time']); $end = $start->copy()->addMinutes($service->duration_minutes);
        $conflict = Appointment::where('barber_id',$data['barber_id'])->whereDate('date',$data['date'])->where('status','!=','canceled')->where('start_time','<',$end->format('H:i:s'))->where('end_time','>',$start->format('H:i:s'))->exists();
        if ($conflict) return back()->withErrors(['start_time' => 'Ese horario acaba de ser reservado.'])->withInput();
        $appointment = DB::transaction(fn () => Appointment::create([...$data, 'end_time'=>$end->format('H:i:s'), 'status'=>'pending']));
        return view('booking.confirmation', compact('appointment'))->with('barber', $appointment->barber)->with('service', $service);
    }
}
