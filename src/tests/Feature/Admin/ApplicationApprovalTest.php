<?php

namespace Tests\Feature\Admin;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplicationApprovalTest extends TestCase
{
    use RefreshDatabase;

   
    private function makeUsers(): array
    {
        $admin = User::factory()->create([
            'name'              => '管理者',
            'email'             => 'admin@example.com',
            'role'              => 1,
            'email_verified_at' => now(),
        ]);

        $user = User::factory()->create([
            'name'              => '山田太郎',
            'email'             => 'taro@example.com',
            'role'              => 0,
            'email_verified_at' => now(),
        ]);

        return [$admin, $user];
    }

    
    private function makeAttendance(User $user): Attendance
    {
        return Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => '2025-11-11',
            'clock_in'  => '2025-11-11 09:00:00',
            'clock_out' => '2025-11-11 18:00:00',
            'note'      => '元の備考',
        ]);
    }

   
    private function makePendingApplication(User $user, Attendance $attendance): AttendanceApplication
    {
        AttendanceApplication::unguard();

        return AttendanceApplication::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'type'          => 1,
            'work_date'     => $attendance->work_date,
            'clock_in'      => '2025-11-11 09:30:00',
            'clock_out'     => '2025-11-11 18:15:00',                     
            'note'          => 'ユーザーによる修正申請',
            'status'        => 0,                       
        ]);
    }

    
    private function makeDetailApplication(User $user, Attendance $attendance): AttendanceApplication
    {
        AttendanceApplication::unguard();

        return AttendanceApplication::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'type'          => 1,
            'work_date'     => $attendance->work_date,
            'clock_in'      => '2025-11-11 10:00:00',
            'clock_out'     => '2025-11-11 19:00:00',
            'note'          => '10:00〜19:00 に修正',
            'status'        => 0,
        ]);
    }

   
    private function makeApprovalApplication(User $user, Attendance $attendance): AttendanceApplication
    {
        AttendanceApplication::unguard();

        return AttendanceApplication::create([
            'attendance_id' => $attendance->id,
            'user_id'       => $user->id,
            'type'          => 1,
            'work_date'     => $attendance->work_date,
            'clock_in'      => '2025-11-11 09:30:00',
            'clock_out'     => '2025-11-11 18:15:00',
            'note'          => '9:30〜18:15に修正をお願いします',
            'status'        => 0,
        ]);
    }

   
    public function test_承認待ち一覧に修正申請が表示される()
    {
        [$admin, $user] = $this->makeUsers();
        $att = $this->makeAttendance($user);
        $app = $this->makePendingApplication($user, $att);

        $res = $this->actingAs($admin)->get(route('admin.apps.index'));

        $res->assertStatus(200);
        $html = $res->getContent();

        $this->assertTrue(str_contains($html, '山田太郎'));
        $this->assertTrue(
            str_contains($html, '2025-11-11') ||
            str_contains($html, '2025/11/11') ||
            str_contains($html, '2025年11月11日')
        );
        $this->assertTrue(
            str_contains($html, '承認待ち') || str_contains($html, '承認待')
        );
    }

    
    public function test_修正申請の詳細が正しく表示される()
    {
        [$admin, $user] = $this->makeUsers();
        $att = $this->makeAttendance($user);
        $app = $this->makeDetailApplication($user, $att);

        $res = $this->actingAs($admin)->get(route('admin.apps.show', $app));

        $res->assertStatus(200);
        $html = $res->getContent();

        $this->assertTrue(str_contains($html, '山田太郎'));
        $this->assertTrue(
            str_contains($html, '2025-11-11') ||
            str_contains($html, '2025/11/11') ||
            str_contains($html, '2025年11月11日')
        );
        $this->assertTrue(
            str_contains($html, '10:00') || str_contains($html, '10時')
        );
        $this->assertTrue(
            str_contains($html, '19:00') || str_contains($html, '19時')
        );
    }

   
    public function test_修正申請の承認処理が正しく行われる()
    {
        [$admin, $user] = $this->makeUsers();
        $att = $this->makeAttendance($user);
        $app = $this->makeApprovalApplication($user, $att);

        $res = $this->actingAs($admin)->post(
            route('admin.apps.approve', $app),
            ['manager_comment' => '承認しました']
        );

        $res->assertStatus(302); 

        $app->refresh();
        $att->refresh();

      
        $this->assertSame(1, (int) $app->status);
        $this->assertNotNull($app->approved_at);
        $this->assertSame($admin->id, (int) $app->approved_by);

        
        $this->assertSame('09:30', optional($att->clock_in)->format('H:i'));
        $this->assertSame('18:15', optional($att->clock_out)->format('H:i'));
        $this->assertSame('9:30〜18:15に修正をお願いします', (string) $att->note);

       
        $this->assertSame('承認しました', (string) $app->manager_comment);
    }
}

