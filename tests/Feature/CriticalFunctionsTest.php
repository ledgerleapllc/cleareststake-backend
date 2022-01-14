<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CriticalFunctionsTest extends TestCase
{
    public function testLogin() {
        $user = [
            'email' => 'demo@devxdao.com',
            'password' => 'AdminTest',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/login', $user);

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                        'user',
                    ]);
    }

    public function testInviteUser() {
        $token = $this->getToken();

        $user = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'demouser@devxdao.com',
            'balance' => 1000,
            'in_fund' => 1,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/user', $user);

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                    ]);
    }

    public function testGetMe() {
        $token = $this->getToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/me');

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                        'me',
                    ]);
    }

    public function testSendResetEmail() {
        $token = $this->getToken();
        $user = $this->addUser();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('post', '/api/common/send-reset-email', [
            'email' => $user->email
        ]);

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                    ]);
    }

    public function testUserWithdraw() {
        $this->addUser();
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('put', '/api/user/withdraw', [
            'amount' => 100
        ]);

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                    ]);
    }

    public function testUserGraphInfo() {
        $this->addUser();
        $token = $this->getUserToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/user/graph-info');

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                    ]);
    }

    public function testAdminUsers() {
        $token = $this->getToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/admin/users');

        // $apiResponse = $response->baseResponse->getData();
        
        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                        'total',
                        'users',
                        'total_balance',
                    ]);
    }  

    public function testAdminAllUsers() {
        $token = $this->getToken();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $token,
        ])->json('get', '/api/admin/users/all');

        // $apiResponse = $response->baseResponse->getData();
        
        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'success',
                        'users',
                    ]);
    }  

    
}
