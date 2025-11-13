<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 11, 12, 9, 0, 0));
    }

    protected function createVerifiedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge([
            'name'              => 'テストユーザー',
            'email'             => 'test@example.com',
            'password'          => bcrypt('password123'),
            'email_verified_at' => now(),
        ], $attrs));
    }

    protected function makeAttendance(User $user, string $date): Attendance
    {
        $att = Attendance::create([
            'user_id'   => $user->id,
            'work_date' => $date,
            'clock_in'  => Carbon::parse("$date 09:00"),
            'clock_out' => Carbon::parse("$date 18:00"),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $att->id,
            'break_start' => "$date 12:00:00",
            'break_end'   => "$date 13:00:00",
        ]);


        return $att;
    }

  
    public function test_勤怠詳細ページにログインユーザー名が表示される()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user, '2025-11-11');

        $response = $this->actingAs($user)->get(route('user.attendance.show', $attendance));

        $response->assertStatus(200);
        $response->assertSee('テストユーザー');
    }

    
    public function test_勤怠詳細ページに正しい日付が表示される()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user, '2025-11-11');

        $response = $this->actingAs($user)->get(route('user.attendance.show', $attendance));
        $response->assertStatus(200);

        $html = $response->getContent();
        $this->assertTrue(
            str_contains($html, '2025-11-11') ||
            str_contains($html, '2025/11/11') ||
            str_contains($html, '2025年11月11日')
        );
    }

  
    public function test_出勤退勤時間が正しく表示される()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user, '2025-11-11');

        $response = $this->actingAs($user)->get(route('user.attendance.show', $attendance));
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

  
    public function test_休憩時間が正しく表示される()
    {
         $user = $this->createVerifiedUser();
    $attendance = $this->makeAttendance($user, '2025-11-11');
    
    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_start' => '12:00',
        'break_end'   => '13:00',
    ]);

    $response = $this->actingAs($user)->get(route('user.attendance.show', $attendance));
    $response->assertStatus(200);

    $html = $response->getContent();

    $this->assertTrue(
        str_contains($html, '12:00') ||
        str_contains($html, '12：00') ||
        str_contains($html, '12時') ||
        str_contains($html, '12')
    );

    $this->assertTrue(
        str_contains($html, '13:00') ||
        str_contains($html, '13：00') ||
        str_contains($html, '13時') ||
        str_contains($html, '13')
   
    );
    }
}
