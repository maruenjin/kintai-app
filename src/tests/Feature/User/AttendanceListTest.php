<?php

namespace Tests\Feature\User;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        Carbon::setTestNow(Carbon::create(2025, 11, 11, 9, 0, 0));
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

    protected function today(): string
    {
        return Carbon::today()->toDateString();
    }

    protected function makeAttendance(User $user, string $date, ?string $in = null, ?string $out = null): Attendance
    {
        return Attendance::firstOrCreate(
            [
                'user_id'   => $user->id,
                'work_date' => $date,
            ],
            [
                'clock_in'  => $in  ? Carbon::parse("$date $in")  : null,
                'clock_out' => $out ? Carbon::parse("$date $out") : null,
            ]
        );
    }

    
    public function test_一覧は自分の勤怠のみが対象になる()
    {
        $me   = $this->createVerifiedUser(['email' => 'me@example.com']);
        $other= $this->createVerifiedUser(['email' => 'other@example.com']);

        
        $mine  = $this->makeAttendance($me,    '2025-11-05', '09:00', '18:00');
        $their = $this->makeAttendance($other, '2025-11-05', '10:10', '18:10');

        
        $response = $this->actingAs($me)->get(route('user.attendance.list'));

        $response->assertStatus(200);

       
        $response->assertSee(route('user.attendance.show', $mine));

       
        $response->assertDontSee(route('user.attendance.show', $their));
    }

    
    public function test_当月表示と月切替が機能する()
    {
        $me = $this->createVerifiedUser();

       
        $oct = $this->makeAttendance($me, '2025-10-03', '09:30', null);
        $nov = $this->makeAttendance($me, '2025-11-05', '09:00', null);

      
        $resNov = $this->actingAs($me)->get(route('user.attendance.list'));
        $resNov->assertStatus(200);

        $htmlNov = $resNov->getContent();
       
        $this->assertTrue(
            str_contains($htmlNov, '2025-11') ||
            str_contains($htmlNov, '2025/11') ||
            str_contains($htmlNov, '2025年11月')
        );

        
        $resNov->assertSee(route('user.attendance.show', $nov));
        
        $resNov->assertDontSee(route('user.attendance.show', $oct));

       
        $resOct = $this->actingAs($me)->get(route('user.attendance.list', ['ym' => '2025-10']));
        $resOct->assertStatus(200);

        $htmlOct = $resOct->getContent();
        $this->assertTrue(
            str_contains($htmlOct, '2025-10') ||
            str_contains($htmlOct, '2025/10') ||
            str_contains($htmlOct, '2025年10月')
        );

       
        $resOct->assertSee(route('user.attendance.show', $oct));
       
        $resOct->assertDontSee(route('user.attendance.show', $nov));
    }

   
    public function test_詳細リンクで該当日の勤怠詳細に遷移できる()
    {
        $me = $this->createVerifiedUser();

        $nov = $this->makeAttendance($me, '2025-11-05', '09:00', '18:00');

        
        $res = $this->actingAs($me)->get(route('user.attendance.list'));
        $res->assertStatus(200);
        $res->assertSee(route('user.attendance.show', $nov));

     
        $show = $this->actingAs($me)->get(route('user.attendance.show', $nov));
        $show->assertStatus(200);

       
        $html = $show->getContent();
        $this->assertTrue(
            str_contains($html, '2025-11-05') ||
            str_contains($html, '2025/11/05') ||
            str_contains($html, '2025年11月5日')
        );
    }
}

