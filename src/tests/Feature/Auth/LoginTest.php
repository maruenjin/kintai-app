<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    
    protected function createUser(): User
    {
        return User::factory()->create([
            'name'              => 'テストユーザー',
            'email'             => 'test@example.com',
            'password'          => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);
    }

   
    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $this->createUser();

        $response = $this->post('/login', [
            'email'    => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

   
    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $this->createUser();

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

    
    public function test_登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        $this->createUser();

        
        $response = $this->post('/login', [
            'email'    => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first('email')
        );
    }

   
    public function test_正しい情報ならログインできる()
    {
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'password123',
        ]);

       
        $this->assertAuthenticatedAs($user);

       
        $response->assertStatus(302);
    }
}
