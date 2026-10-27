<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    private function ensureAdmin(): void
    {
        abort_unless(auth()->user()->role === 'admin', 403);
    }

    public function index()
    {
        $this->ensureAdmin();
        return view('admin.index', [
            'services' => Service::orderBy('name')->get(),
            'barbers' => Barber::with('schedules')->orderBy('name')->get(),
        ]);
    }

    public function serviceStore(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:services,name',
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:240',
        ]);
        Service::create($data + ['is_active' => true]);
        return back()->with('success', 'Servicio creado.');
    }

    public function serviceUpdate(Request $request, Service $service)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:120|unique:services,name,' . $service->id,
            'description' => 'nullable|string|max:500',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:15|max:240',
            'is_active' => 'nullable|boolean',
        ]);
        $service->update($data + ['is_active' => $request->boolean('is_active')]);
        return back()->with('success', 'Servicio actualizado.');
    }

    public function serviceDestroy(Service $service)
    {
        $this->ensureAdmin();
        abort_if($service->appointments()->exists(), 422, 'No se puede eliminar un servicio con turnos asociados.');
        $service->delete();
        return back()->with('success', 'Servicio eliminado.');
    }

    public function barberStore(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate([
            'name' => 'required|string|max:120', 'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:40', 'password' => 'required|string|min:8',
        ]);
        DB::transaction(function () use ($data) {
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => Hash::make($data['password']), 'role' => 'barber']);
            Barber::create(['user_id' => $user->id, 'name' => $data['name'], 'phone' => $data['phone'] ?? null, 'is_active' => true]);
        });
        return back()->with('success', 'Barbero creado.');
    }

    public function barberUpdate(Request $request, Barber $barber)
    {
        $this->ensureAdmin();
        $data = $request->validate(['name' => 'required|string|max:120', 'phone' => 'nullable|string|max:40', 'is_active' => 'nullable|boolean']);
        $barber->update($data + ['is_active' => $request->boolean('is_active')]);
        $barber->user?->update(['name' => $data['name']]);
        return back()->with('success', 'Barbero actualizado.');
    }

    public function schedules(Request $request, Barber $barber)
    {
        $this->ensureAdmin();
        $data = $request->validate(['schedules' => 'array', 'schedules.*.enabled' => 'nullable|boolean', 'schedules.*.start_time' => 'nullable|date_format:H:i', 'schedules.*.end_time' => 'nullable|date_format:H:i', 'schedules.*.break_start' => 'nullable|date_format:H:i', 'schedules.*.break_end' => 'nullable|date_format:H:i']);
        foreach (range(0, 6) as $day) {
            $row = $data['schedules'][$day] ?? [];
            $valid = !empty($row['enabled']);
            if ($valid) {
                validator($row, ['start_time' => 'required|date_format:H:i', 'end_time' => 'required|date_format:H:i'])->validate();
                BarberSchedule::updateOrCreate(['barber_id' => $barber->id, 'day_of_week' => $day], ['start_time' => $row['start_time'], 'end_time' => $row['end_time'], 'break_start' => $row['break_start'] ?? null, 'break_end' => $row['break_end'] ?? null]);
            } else {
                BarberSchedule::where(['barber_id' => $barber->id, 'day_of_week' => $day])->delete();
            }
        }
        return back()->with('success', 'Horarios actualizados.');
    }

    public function appointmentStore(Request $request)
    {
        $this->ensureAdmin();
        $data = $request->validate(['barber_id' => 'required|exists:barbers,id', 'service_id' => 'required|exists:services,id', 'client_name' => 'required|string|max:120', 'client_phone' => 'required|string|max:40', 'date' => 'required|date', 'start_time' => 'required|date_format:H:i']);
        $service = Service::findOrFail($data['service_id']);
        $start = Carbon::parse($data['date'] . ' ' . $data['start_time']);
        $end = $start->copy()->addMinutes($service->duration_minutes);
        $conflict = Appointment::where('barber_id', $data['barber_id'])->whereDate('date', $data['date'])->where('status', '!=', 'canceled')->where('start_time', '<', $end->format('H:i:s'))->where('end_time', '>', $start->format('H:i:s'))->exists();
        if ($conflict) return back()->withErrors(['start_time' => 'Ese horario ya está ocupado.'])->withInput();
        Appointment::create($data + ['end_time' => $end->format('H:i:s'), 'status' => 'pending']);
        return redirect()->route('dashboard', ['date' => $data['date']])->with('success', 'Turno cargado.');
    }
}
