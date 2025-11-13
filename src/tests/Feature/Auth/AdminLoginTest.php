<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    
    protected function createAdmin(): User
    {
        return User::factory()->create([
            'name'              => '管理者',
            'email'             => 'admin@example.com',
            'password'          => bcrypt('adminpass'),
            'role'              => 1, 
            'email_verified_at' => now(),
        ]);
    }

    
    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $this->createAdmin();

        $response = $this->post('/admin/login', [
            'email'    => '',
            'password' => 'adminpass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

    
    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $this->createAdmin();

        $response = $this->post('/admin/login', [
            'email'    => 'admin@example.com',
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
        $this->createAdmin();

        
        $response = $this->post('/admin/login', [
            'email'    => 'wrong@example.com',
            'password' => 'adminpass',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertEquals(
            'ログイン情報が登録されていません',
            session('errors')->first('email')
        );
    }

   
    public function test_正しい管理者情報ならログインできる()
    {
        $admin = $this->createAdmin();

        $response = $this->post('/admin/login', [
            'email'    => 'admin@example.com',
            'password' => 'adminpass',
        ]);

        $this->assertAuthenticatedAs($admin);

       
        $response->assertRedirect(route('admin.home'));
    }
}
