<?php
namespace App\Http\Controllers;
use App\Models\Appointment; use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function index(Request $request) { $date=$request->date ?: now()->toDateString(); $query=Appointment::with(['barber','service'])->whereDate('date',$date); if(auth()->user()->role==='barber') $query->where('barber_id',auth()->user()->barber?->id); return view('dashboard.index',['appointments'=>$query->orderBy('start_time')->get(),'date'=>$date]); }
    public function status(Request $request, Appointment $appointment) { abort_unless(auth()->user()->role==='admin'||auth()->user()->barber?->id===$appointment->barber_id,403); $request->validate(['status'=>'required|in:pending,completed,canceled']); $appointment->update(['status'=>$request->status]); return back(); }
}
