<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    
    public function test_名前が未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'                  => '',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        
        $response->assertSessionHasErrors('name');

        
        $this->assertEquals(
            'お名前を入力してください',
            session('errors')->first('name')
        );
    }

    
    public function test_メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'                  => 'テスト太郎',
            'email'                 => '',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertEquals(
            'メールアドレスを入力してください',
            session('errors')->first('email')
        );
    }

   
    public function test_パスワードが8文字未満の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'                  => 'テスト太郎',
            'email'                 => 'test@example.com',
            'password'              => 'pass', 
            'password_confirmation' => 'pass',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードは8文字以上で入力してください',
            session('errors')->first('password')
        );
    }

    
    public function test_パスワードが一致しない場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'                  => 'テスト太郎',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password999',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードと一致しません',
            session('errors')->first('password')
        );
    }

   
    public function test_パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register', [
            'name'                  => 'テスト太郎',
            'email'                 => 'test@example.com',
            'password'              => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');

        $this->assertEquals(
            'パスワードを入力してください',
            session('errors')->first('password')
        );
    }

   
    public function test_フォームが正しく入力されていればユーザーが保存される()
    {
        $response = $this->post('/register', [
            'name'                  => 'テスト太郎',
            'email'                 => 'test@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

      
        $response->assertStatus(302);
        
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name'  => 'テスト太郎',
        ]);
    }
}

