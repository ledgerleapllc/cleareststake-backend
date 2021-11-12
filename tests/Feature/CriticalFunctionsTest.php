<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CriticalFunctionsTest extends TestCase
{
    public function testInvitationCode(): void
    {
        $response = $this->get('/api/invitation/123');
        $response->assertStatus(200);
    }

    public function testInvitation(): void
    {
        $response = $this->put('/api/invitation');
        $response->assertStatus(200);
    }

    public function testLogin(): void
    {
        $response = $this->post('/api/login');
        $response->assertStatus(200);
    }

    public function testSendResetEmail(): void
    {
        $response = $this->post('/api/common/send-reset-email');
        $response->assertStatus(200);
    }

    public function testResetPassword(): void
    {
        $response = $this->post('/api/common/reset-password');
        $response->assertStatus(200);
    }

    public function testMe(): void
    {
        $response = $this->get('/api/me');
        $response->assertStatus(302);
    }

    public function testUser(): void
    {
        $response = $this->post('/api/user');
        $response->assertStatus(302);
    }

    public function testUserWithdraw(): void
    {
        $response = $this->put('/api/user/withdraw');
        $response->assertStatus(302);
    }

    public function testUserGraphInfo(): void
    {
        $response = $this->get('/api/user/graph-info');
        $response->assertStatus(302);
    }

    public function testAdminUsers(): void
    {
        $response = $this->get('/api/admin/users');
        $response->assertStatus(302);
    }

    public function testDdminUsersAll(): void
    {
        $response = $this->get('/api/admin/users/all');
        $response->assertStatus(302);
    }

    public function testAdminUser(): void
    {
        $response = $this->get('/api/admin/user/1');
        $response->assertStatus(302);
    }

    public function testAdminValues(): void
    {
        $response = $this->get('/api/admin/values');
        $response->assertStatus(302);
    }

    public function testAdminUserExportCsv(): void
    {
        $response = $this->get('/api/admin/user/1/export-csv');
        $response->assertStatus(302);
    }

    public function testAdminCsprPrice(): void
    {
        $response = $this->get('/api/admin/cspr-price');
        $response->assertStatus(302);
    }

    public function testAdminBalance(): void
    {
        $response = $this->put('/api/admin/balance');
        $response->assertStatus(302);
    }

    public function testAdminWithdraw(): void
    {
        $response = $this->put('/api/admin/withdraw');
        $response->assertStatus(302);
    }

    public function testAdminDeposit(): void
    {
        $response = $this->put('/api/admin/deposit');
        $response->assertStatus(302);
    }

    public function testAdminUserFund(): void
    {
        $response = $this->put('/api/admin/user/1/fund');
        $response->assertStatus(302);
    }

    public function testAdminResetUserPassword(): void
    {
        $response = $this->post('/api/admin/reset-user-password');
        $response->assertStatus(302);
    }

    public function testAdminUsersExportCsv(): void
    {
        $response = $this->get('/api/admin/users/export-csv');
        $response->assertStatus(302);
    }

    public function testAdminFundSale(): void
    {
        $response = $this->post('/api/admin/fund-sale');
        $response->assertStatus(302);
    }

    public function testCommonSettings(): void
    {
        $response = $this->get('/api/common/settings');
        $response->assertStatus(302);
    }

    public function testCommonTransactions(): void
    {
        $response = $this->get('/api/common/transactions');
        $response->assertStatus(302);
    }

    public function testCommonLogs(): void
    {
        $response = $this->get('/api/common/logs');
        $response->assertStatus(302);
    }

    public function testCommonSendHelpRequest(): void
    {
        $response = $this->post('/api/common/send-help-request');
        $response->assertStatus(302);
    }

    public function testCommonChangeEmail(): void
    {
        $response = $this->post('/api/common/change-email');
        $response->assertStatus(302);
    }

    public function testCommonChangePassword(): void
    {
        $response = $this->post('/api/common/change-password');
        $response->assertStatus(302);
    }

    public function testCommonTransactionsExportCsv(): void
    {
        $response = $this->get('/api/common/transactions/export-csv');
        $response->assertStatus(302);
    }
}
