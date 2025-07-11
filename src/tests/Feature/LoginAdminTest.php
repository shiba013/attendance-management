<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginAdminTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_email_is_required()
    {
        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_password_is_required()
    {
        $response = $this->post('/admin/login', [
            'email' => 'test@test.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_information_discrepancy()
    {
        $user = User::factory()->create([
            'email' => 'ok@test.com',
            'password' => bcrypt('password0123'),
            'role' => 1,
        ]);
        $response = $this->post('/admin/login', [
            'email' => 'bad@test.com',
            'password' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
