<?php

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2025, 11, 12, 9, 0, 0));
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

   
    public function test_詳細画面が選択した勤怠を表示している()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎', 'taro@example.com');

        $date = '2025-11-11';
        $att  = $this->makeAttendance($staff, $date, '09:00', '18:00');

        $res = $this->actingAs($admin)->get(route('admin.attendance.show', $att));
        $res->assertStatus(200);

        $html = $res->getContent();
        $this->assertTrue(
            str_contains($html, '山田太郎') ||
            str_contains($html, $staff->name)
        );
        $this->assertTrue(
            str_contains($html, '2025-11-11') ||
            str_contains($html, '2025/11/11') ||
            str_contains($html, '2025年11月11日')
        );
    }

    
    public function test_出勤時間が退勤より後ならエラー()
    {
       $admin = $this->createAdmin();
    $staff = $this->createStaff('山田太郎', 'taro@example.com');

    $att = $this->makeAttendance($staff, '2025-11-11', '09:00', '18:00');

    $res = $this->actingAs($admin)->put(route('admin.attendance.update', $att), [
        'clock_in_time'  => '19:00',
        'clock_out_time' => '18:00',
        'breaks' => [
            ['start' => null, 'end' => null],
            ['start' => null, 'end' => null],
        ],
        'note' => '管理修正',
    ]);

    $res->assertSessionHasErrors();
}
  
    public function test_休憩開始が退勤より後ならエラー()
    {
        $admin = $this->createAdmin();
    $staff = $this->createStaff('山田太郎', 'taro@example.com');

    $att = $this->makeAttendance($staff, '2025-11-11', '09:00', '18:00');

    $res = $this->actingAs($admin)->put(route('admin.attendance.update', $att), [
        'clock_in_time'  => '09:00',
        'clock_out_time' => '18:00',
        'breaks' => [
            ['start' => '19:00', 'end' => '19:30'],
            ['start' => null, 'end' => null],
        ],
        'note' => '管理修正',
    ]);

    $res->assertSessionHasErrors();
    
    }

  
    public function test_休憩終了が退勤より後ならエラー()
    {
        $admin = $this->createAdmin();
    $staff = $this->createStaff('山田太郎', 'taro@example.com');

    $att = $this->makeAttendance($staff, '2025-11-11', '09:00', '18:00');

    $res = $this->actingAs($admin)->put(route('admin.attendance.update', $att), [
        'clock_in_time'  => '09:00',
        'clock_out_time' => '18:00',
        'breaks' => [
            ['start' => '17:30', 'end' => '19:00'],
            ['start' => null, 'end' => null],
        ],
        'note' => '管理修正',
    ]);

    $res->assertSessionHasErrors();
    }

  
    public function test_備考未入力ならエラー()
    {
        $admin = $this->createAdmin();
        $staff = $this->createStaff('山田太郎', 'taro@example.com');

        $date = '2025-11-11';
        $att  = $this->makeAttendance($staff, $date, '09:00', '18:00');

        $res = $this->actingAs($admin)->put(route('admin.attendance.update', $att), [
            'clock_in'      => '09:00',
            'clock_out'     => '18:00',
            'break1_start'  => null,
            'break1_end'    => null,
            'break2_start'  => null,
            'break2_end'    => null,
            'note'          => '', 
        ]);

        $res->assertSessionHasErrors(['note']);
    }

    
    public function test_正常に更新できる()
    {
        $admin = $this->createAdmin();
    $staff = $this->createStaff('山田太郎', 'taro@example.com');

    $att = $this->makeAttendance($staff, '2025-11-11', '09:00', '18:00');

    $res = $this->actingAs($admin)->put(route('admin.attendance.update', $att), [
        'clock_in_time'  => '09:30',
        'clock_out_time' => '18:15',
        'breaks' => [
            ['start' => '12:00', 'end' => '12:30'],
            ['start' => null, 'end' => null],
        ],
        'note' => '管理者による修正',
    ]);

    $res->assertSessionHasNoErrors();

    $att->refresh();
    $this->assertSame('09:30', optional($att->clock_in)->format('H:i'));
    $this->assertSame('18:15', optional($att->clock_out)->format('H:i'));
    $this->assertSame('管理者による修正', (string)$att->note);
    }
}
