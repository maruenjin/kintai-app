<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\AttendanceBreak; 

class AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function createVerifiedUser(): User
    {
        return User::factory()->create([
            'name'              => 'テストユーザー',
            'email'             => 'test@example.com',
            'password'          => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
    }

    protected function today(): string
    {
        return Carbon::today()->toDateString();
    }

    
    public function test_勤務外の場合ステータスが勤務外と表示される()
    {
        $user = $this->createVerifiedUser();

       
        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    
    public function test_出勤中の場合ステータスが出勤中と表示される()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $this->today(),
            'clock_in'  => Carbon::parse($this->today() . ' 09:00'),
            'status'    => Attendance::STATUS_WORKING ?? 1, 
        ]);

        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    
    public function test_休憩中の場合ステータスが休憩中と表示される()
    {
         $user = $this->createVerifiedUser();

    
        $attendance = Attendance::firstOrCreate(
        [
            'user_id'   => $user->id,
            'work_date' => $this->today(),
        ],
        [
            'clock_in' => Carbon::parse($this->today() . ' 09:00'),
            'status'   => Attendance::STATUS_WORKING,
        ]
    );

    
    AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_start'   => Carbon::parse($this->today() . ' 12:00'),
        'break_end'     => null,  
    ]);

   
    $response = $this->actingAs($user)->get(route('user.attendance.index'));

    $response->assertStatus(200);
    $response->assertSee('休憩中');
    }

    
    public function test_退勤済の場合ステータスが退勤済と表示される()
    {
        $user = $this->createVerifiedUser();

        Attendance::create([
            'user_id'    => $user->id,
            'work_date'  => $this->today(),
            'clock_in'   => Carbon::parse($this->today() . ' 09:00'),
            'clock_out'  => Carbon::parse($this->today() . ' 18:00'),
            'status'     => Attendance::STATUS_DONE ?? 3,
        ]);

        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
