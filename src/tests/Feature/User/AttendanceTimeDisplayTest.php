<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTimeDisplayTest extends TestCase
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

   
    public function test_勤怠打刻画面に現在の日付が表示されている()
    {
        
        $today = Carbon::now()->isoFormat('YYYY年M月D日'); 

        $user = $this->createVerifiedUser();

        
        $response = $this->actingAs($user)->get(route('user.attendance.index'));

        $response->assertStatus(200);

        
        $response->assertSee($today);
    }
}
