<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AttendanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        
        $users = collect([
            ['name' => '山田太郎', 'email' => 'yamada@example.com', 'role' => 1], 
            ['name' => '佐藤花子', 'email' => 'sato@example.com',   'role' => 0],
            ['name' => '鈴木次郎', 'email' => 'suzuki@example.com', 'role' => 0],
            ['name' => '田中三郎', 'email' => 'tanaka@example.com', 'role' => 0],
        ])->map(function ($u) {
            return User::factory()->create([
                'name'     => $u['name'],
                'email'    => $u['email'],
                'password' => Hash::make('password'),
                'role'     => $u['role'],   
            ]);
        });

       
        foreach ($users as $user) {
            for ($i = 0; $i < 20; $i++) {
                $date = Carbon::today()->subDays($i);

                if ($date->isWeekend()) continue;

                $clockIn  = $date->copy()->setTime(8, 30)->addMinutes(rand(0, 60));
                $clockOut = $date->copy()->setTime(17, 30)->addMinutes(rand(0, 60));

                Attendance::create([
                    'user_id'   => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'clock_in'  => $clockIn,
                    'clock_out' => $clockOut,
                    'status'    => Attendance::STATUS_DONE,
                    'note'      => ['通常勤務', '外回りあり', 'リモート勤務'][rand(0, 2)],
                ]);
            }
        }
    }
}
