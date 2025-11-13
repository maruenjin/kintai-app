<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
       
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 9, 0, 0));
    }

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

    
    public function test_勤務外の場合出勤ボタンが表示される()
    {
        $user = $this->createVerifiedUser();

        
        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);

        
        $response->assertSee('勤務外');
        $response->assertSee('出勤');
    }

   
    public function test_出勤処理後ステータスが出勤中になる()
    {
        $user = $this->createVerifiedUser();

       
        $before = $this->actingAs($user)->get(route('user.attendance.index'));
        $before->assertSee('勤務外');

       
        $response = $this->actingAs($user)->post(route('user.attendance.clockin'));

        $response->assertStatus(302); 

       
        $this->assertDatabaseHas('attendances', [
            'user_id'   => $user->id,
            'work_date' => $this->today(),
        ]);

       
        $after = $this->actingAs($user)->get(route('user.attendance.index'));
        $after->assertStatus(200);
       
        $after->assertSee('出勤中');
    }

    
    public function test_退勤済の場合出勤ボタンが表示されない()
    {
        $user = $this->createVerifiedUser();

        
        Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $this->today(),
            'clock_in'  => Carbon::parse($this->today() . ' 09:00'),
            'clock_out' => Carbon::parse($this->today() . ' 18:00'),
            'status'    => Attendance::STATUS_DONE ?? 3,
        ]);

        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('退勤済');
        
        $response->assertDontSee('出勤');
    }

    
    public function test_出勤後勤怠一覧画面に出勤時刻が表示される()
    {
        $user = $this->createVerifiedUser();

        
        $this->actingAs($user)->post(route('user.attendance.clockin'));

       
        $response = $this->actingAs($user)->get(route('user.attendance.list'));

        $response->assertStatus(200);

        
        $response->assertSee('09:00');
    }
}

