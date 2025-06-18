<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@test.com',
            'password' => 'password0123',
            'password_confirmation' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザ',
            'email' => '',
            'password' => 'password0123',
            'password_confirmation' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_password_is_under7_characters()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザ',
            'email' => 'test@test.com',
            'password' => 'pass',
            'password_confirmation' => 'pass'
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
        ]);
    }

    public function test_password_is_mismatch()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザ',
            'email' => 'test@test.com',
            'password' => 'password0123',
            'password_confirmation' => 'password9876'
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません',
        ]);
    }

    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザ',
            'email' => 'test@test.com',
            'password' => '',
            'password_confirmation' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_success()
    {
        $response = $this->post('/register', [
            'name' => 'テストユーザ',
            'email' => 'test@test.com',
            'password' => 'password0123',
            'password_confirmation' => 'password0123',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
        ]);
        $response->assertRedirect('/email/verify');
    }
}
