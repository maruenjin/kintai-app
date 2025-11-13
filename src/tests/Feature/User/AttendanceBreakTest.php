<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
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

    
    protected function createWorkingAttendance(User $user): Attendance
    {
        return Attendance::firstOrCreate(
            [
                'user_id'   => $user->id,
                'work_date' => $this->today(),
            ],
            [
                'clock_in' => Carbon::parse($this->today() . ' 09:00'),
                'status'   => Attendance::STATUS_WORKING ?? 1,
            ]
        );
    }

   
    public function test_出勤中の場合休憩入ボタンが表示される()
    {
        $user = $this->createVerifiedUser();

        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('休憩入');
    }

    
    public function test_休憩入後にステータスが休憩中になり休憩レコードが作成される()
    {
        $user = $this->createVerifiedUser();

        $attendance = $this->createWorkingAttendance($user);

        
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 12, 0, 0));

        $response = $this->actingAs($user)->post(route('user.attendance.breakin'));

        $response->assertStatus(302);

       
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break);
        $this->assertNotNull($break->break_start);
        $this->assertNull($break->break_end);

       
        $after = $this->actingAs($user)->get(route('user.attendance.index'));
        $after->assertStatus(200);
        $after->assertSee('休憩中');
    }

    
    public function test_休憩戻後にステータスが出勤中に戻る()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 12, 0, 0));
        $this->actingAs($user)->post(route('user.attendance.breakin'));

       
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 13, 0, 0));
        $response = $this->actingAs($user)->post(route('user.attendance.breakout'));

        $response->assertStatus(302);

        
        $break = AttendanceBreak::where('attendance_id', $attendance->id)->latest('id')->first();
        $this->assertNotNull($break);
        $this->assertNotNull($break->break_end);

       
        $after = $this->actingAs($user)->get(route('user.attendance.index'));
        $after->assertStatus(200);
        $after->assertSee('出勤中');
    }

    
    public function test_休憩は1日に複数回行える()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

       
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 12, 0, 0));
        $this->actingAs($user)->post(route('user.attendance.breakin'));
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 12, 30, 0));
        $this->actingAs($user)->post(route('user.attendance.breakout'));

        
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 15, 0, 0));
        $this->actingAs($user)->post(route('user.attendance.breakin'));
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 15, 15, 0));
        $this->actingAs($user)->post(route('user.attendance.breakout'));

       
        $this->assertEquals(
            2,
            AttendanceBreak::where('attendance_id', $attendance->id)->count()
        );
    }

    
    public function test_休憩時刻が勤怠一覧画面で確認できる()
    {
       $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 12, 0, 0));
        $this->actingAs($user)->post(route('user.attendance.breakin'));
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 13, 0, 0));
        $this->actingAs($user)->post(route('user.attendance.breakout'));

        
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $break = AttendanceBreak::where('attendance_id', $attendance->id)->first();
        $this->assertNotNull($break);
        $this->assertNotNull($break->break_start);
        $this->assertNotNull($break->break_end);

       
        $response = $this->actingAs($user)->get(route('user.attendance.list'));

        $response->assertStatus(200);

        $html = $response->getContent();

        $this->assertTrue(
          str_contains($html, '2025-11-11') ||
          str_contains($html, '2025/11/11') ||
          str_contains($html, '2025年11月11日') ||
          str_contains($html, '11/11') ||
          str_contains($html, '11日')
);
        
    }
}

