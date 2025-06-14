<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class LoginUserTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testEmailIsRequired()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function testPasswordIsRequired()
    {
        $response = $this->post('/login', [
            'email' => 'test@test.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function testInformationDiscrepancy()
    {
        $user = User::factory()->create([
            'email' => 'ok@test.com',
            'password' => bcrypt('password0123'),
        ]);
        $response = $this->post('/login', [
            'email' => 'bad@test.com',
            'password' => 'password0123',
        ]);
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }
}
