<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
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

    
    public function test_出勤中の場合退勤ボタンが表示される()
    {
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);
        $response->assertSee('出勤中');
        $response->assertSee('退勤');
    }

  
    public function test_退勤処理後ステータスが退勤済になり退勤時刻が保存される()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->createWorkingAttendance($user);

        
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 18, 0, 0));

        $response = $this->actingAs($user)->post(route('user.attendance.clockout'));

        $response->assertStatus(302);

        $attendance->refresh();

       
        $this->assertNotNull($attendance->clock_out);

       
        $this->assertEquals(
            Attendance::STATUS_DONE ?? 3,
            $attendance->status
        );
    }

   
    public function test_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = $this->createVerifiedUser();
        $this->createWorkingAttendance($user);

       
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 18, 0, 0));
        $this->actingAs($user)->post(route('user.attendance.clockout'));

       
        $response = $this->actingAs($user)->get(route('user.attendance.list'));

        $response->assertStatus(200);

        $html = $response->getContent();

       
        $this->assertTrue(
            str_contains($html, '18:00') ||
            str_contains($html, '18時') ||
            str_contains($html, '18：00')
        );
    }
}
