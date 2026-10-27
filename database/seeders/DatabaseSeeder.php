<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Barber;
use App\Models\BarberSchedule;
use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@barberia.test'], ['name' => 'Administrador', 'password' => 'password', 'role' => 'admin']);
        $user = User::updateOrCreate(['email' => 'barbero@barberia.test'], ['name' => 'Barbero Demo', 'password' => 'password', 'role' => 'barber']);
        $barber = Barber::updateOrCreate(['user_id' => $user->id], ['name' => 'Barbero Demo', 'phone' => '5491112345678', 'bio' => 'Especialista en cortes clásicos y modernos.']);
        foreach ([1,2,3,4,5,6] as $day) BarberSchedule::updateOrCreate(['barber_id' => $barber->id, 'day_of_week' => $day], ['start_time' => '09:00', 'end_time' => '19:00', 'break_start' => '13:00', 'break_end' => '14:00']);
        Service::upsert([
            ['name'=>'Corte clásico','description'=>'Corte y terminación','price'=>8000,'duration_minutes'=>45,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
            ['name'=>'Corte + barba','description'=>'Corte completo con arreglo de barba','price'=>12000,'duration_minutes'=>60,'is_active'=>true,'created_at'=>now(),'updated_at'=>now()],
        ], ['name'], ['description', 'price', 'duration_minutes', 'is_active', 'updated_at']);
    }
}
