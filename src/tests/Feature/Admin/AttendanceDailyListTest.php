<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDailyListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
       
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 9, 0, 0));
    }

    private function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'name'              => '管理者',
            'email'             => 'admin@example.com',
            'password'          => bcrypt('password123'),
            'role'              => 1,           
            'email_verified_at' => now(),
        ], $overrides));
    }

    private function createStaff(string $name, string $email): User
    {
        return User::factory()->create([
            'name'              => $name,
            'email'             => $email,
            'password'          => bcrypt('password123'),
            'role'              => 0,
            'email_verified_at' => now(),
        ]);
    }

    private function makeAttendance(User $user, string $date, ?string $in, ?string $out): Attendance
    {
        return Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date],
            [
                'clock_in'  => $in  ? Carbon::parse("$date $in")  : null,
                'clock_out' => $out ? Carbon::parse("$date $out") : null,
            ]
        );
    }

   
    public function test_当日の全ユーザー勤怠が一覧表示される()
    {
        $admin = $this->createAdmin();

        $u1 = $this->createStaff('山田太郎', 'taro@example.com');
        $u2 = $this->createStaff('佐藤花子', 'hanako@example.com');

        $today = Carbon::today()->toDateString(); 

        $a1 = $this->makeAttendance($u1, $today, '09:00', '18:00');
        $a2 = $this->makeAttendance($u2, $today, '09:30', null);

        $res = $this->actingAs($admin)->get(route('admin.attendance.list'));

        $res->assertStatus(200);

        
        $res->assertSee('山田太郎');
        $res->assertSee('佐藤花子');

       
        $html = $res->getContent();
        $this->assertTrue(
            str_contains($html, '09:00') || str_contains($html, '09時')
        );
        $this->assertTrue(
            str_contains($html, '09:30') || str_contains($html, '09時30分') || str_contains($html, '09:3')
        );
    }

   
    public function test_遷移時に現在の日付が表示される()
    {
        $admin = $this->createAdmin();

        $res = $this->actingAs($admin)->get(route('admin.attendance.list'));
        $res->assertStatus(200);

        $html = $res->getContent();
        $this->assertTrue(
            str_contains($html, '2025-11-11') ||
            str_contains($html, '2025/11/11') ||
            str_contains($html, '2025年11月11日')
        );
    }

  
    public function test_前日翌日ナビゲーションが表示される()
    {
        $admin = $this->createAdmin();

        $res = $this->actingAs($admin)->get(route('admin.attendance.list'));
        $res->assertStatus(200);

       
        $res->assertSee('前日');
        $res->assertSee('翌日');
    }
}
