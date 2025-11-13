<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = Carbon::today();
        $clockIn = $date->copy()->setTime(9, 0);
        $clockOut = $date->copy()->setTime(18, 0);

        return [
            'user_id' => User::factory(),
            'work_date' => $date,
            'clock_in' => $clockIn,
            'clock_out' => $clockOut,
            'note' => 'テストデータ',
            'status' => 3, 
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
