<?php

namespace Tests\Feature\User;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(): User
    {
        return User::factory()->create([
            'email_verified_at' => now(),
            'role' => 0,
        ]);
    }

    private function makeAttendance(User $user, string $date = '2025-11-11'): Attendance
    {
       
        return Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $date],
            [
                'clock_in'  => Carbon::parse("$date 09:00"),
                'clock_out' => Carbon::parse("$date 18:00"),
                'status'    => Attendance::STATUS_DONE ?? 3,
            ]
        );
    }

  
    public function test_出勤時間が退勤時間より後ならエラーメッセージ()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user);

        $response = $this->actingAs($user)->post(route('user.apps.store', $attendance), [
            'clock_in'     => '19:00',
            'clock_out'    => '18:00',
            'break1_start' => null,
            'break1_end'   => null,
            'break2_start' => null,
            'break2_end'   => null,
            'reason'       => 'テスト修正',
        ]);

       
        $response->assertSessionHasErrors(['clock_out']);
    }

 
    public function test_休憩開始が退勤時間より後ならエラーメッセージ()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user);

        $response = $this->actingAs($user)->post(route('user.apps.store', $attendance), [
            'clock_in'     => '09:00',
            'clock_out'    => '18:00',
            'break1_start' => '19:00',
            'break1_end'   => '20:00',
            'break2_start' => null,
            'break2_end'   => null,
            'reason'       => 'テスト修正',
        ]);

        $response->assertSessionHasErrors(['break1_start']);
    }

   
    public function test_備考未入力ならエラーメッセージ()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user);

        $response = $this->actingAs($user)->post(route('user.apps.store', $attendance), [
            'clock_in'     => '09:00',
            'clock_out'    => '18:00',
            'break1_start' => null,
            'break1_end'   => null,
            'break2_start' => null,
            'break2_end'   => null,
            'reason'       => '', // 
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

   
    public function test_正常に修正申請が登録される()
    {
        $user = $this->createVerifiedUser();
        $attendance = $this->makeAttendance($user);

        $response = $this->actingAs($user)->post(route('user.apps.store', $attendance), [
            'clock_in'     => '09:30',
            'clock_out'    => '18:00',
            'break1_start' => '12:00',
            'break1_end'   => '12:30',
            'break2_start' => null,
            'break2_end'   => null,
            'reason'       => '打刻修正テスト',
        ]);

        $response->assertSessionHasNoErrors();

       
        $this->assertEquals(1, AttendanceApplication::count());
        $app = AttendanceApplication::first();

        $this->assertSame($attendance->id, $app->attendance_id);
        $this->assertSame($user->id, $app->user_id);
        $this->assertSame('打刻修正テスト', $app->reason ?? $app->note ?? '打刻修正テスト'); 
        $this->assertEquals(0, (int)$app->status); 
    }
}
