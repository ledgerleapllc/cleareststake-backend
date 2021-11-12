<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;

final class CriticalFunctionsTest extends TestCase
{
    public function testInvitationCode(): void
    {
        $response = $this->get('/invitation/123');
    	$response->assertStatus(200);
    }

    public function testInvitation(): void
    {
        $response = $this->get('/invitation');
    	$response->assertStatus(200);
    }

    public function testLogin(): void
    {
        $response = $this->get('/login');
    	$response->assertStatus(200);
    }

    public function testSendResetEmail(): void
    {
        $response = $this->get('/common/send-reset-email');
    	$response->assertStatus(200);
    }

    public function testResetPassword(): void
    {
        $response = $this->get('/common/reset-password');
    	$response->assertStatus(200);
    }

    public function testMe(): void
    {
        $response = $this->get('/me');
    	$response->assertStatus(200);
    }

    public function testUser(): void
    {
        $response = $this->get('/user');
    	$response->assertStatus(200);
    }

    public function testUserWithdraw(): void
    {
        $response = $this->get('/user/withdraw');
    	$response->assertStatus(200);
    }

    public function testUserGraphInfo(): void
    {
        $response = $this->get('/user/graph-info');
    	$response->assertStatus(200);
    }

    public function testAdminUsers(): void
    {
        $response = $this->get('/admin/users');
    	$response->assertStatus(200);
    }

    public function testDdminUsersAll(): void
    {
        $response = $this->get('/admin/users/all');
    	$response->assertStatus(200);
    }

    public function testAdminUser(): void
    {
        $response = $this->get('/admin/user/1');
    	$response->assertStatus(200);
    }

    public function testAdminValues(): void
    {
        $response = $this->get('/admin/values');
    	$response->assertStatus(200);
    }

    public function testAdminUserExportCsv(): void
    {
        $response = $this->get('/admin/user/1/export-csv');
    	$response->assertStatus(200);
    }

    public function testAdminCsprPrice(): void
    {
        $response = $this->get('/admin/cspr-price');
    	$response->assertStatus(200);
    }

    public function testAdminBalance(): void
    {
        $response = $this->get('/admin/balance');
    	$response->assertStatus(200);
    }

    public function testAdminWithdraw(): void
    {
        $response = $this->get('/admin/withdraw');
    	$response->assertStatus(200);
    }

    public function testAdminDeposit(): void
    {
        $response = $this->get('/admin/deposit');
    	$response->assertStatus(200);
    }

    public function testAdminUserFund(): void
    {
        $response = $this->get('/admin/user/1/fund');
    	$response->assertStatus(200);
    }

    public function testAdminResetUserAassword(): void
    {
        $response = $this->get('/admin/reset-user-password');
    	$response->assertStatus(200);
    }

    public function testAdminUsersExportCsv(): void
    {
        $response = $this->get('/admin/users/export-csv');
    	$response->assertStatus(200);
    }

    public function testAdminFundSale(): void
    {
        $response = $this->get('/admin/fund-sale');
    	$response->assertStatus(200);
    }

    public function testCommonSettings(): void
    {
        $response = $this->get('/common/settings');
    	$response->assertStatus(200);
    }

    public function testCommonTransactions(): void
    {
        $response = $this->get('/common/transactions');
    	$response->assertStatus(200);
    }

    public function testCommonLogs(): void
    {
        $response = $this->get('/common/logs');
    	$response->assertStatus(200);
    }

    public function testCommonSendHelpRequest(): void
    {
        $response = $this->get('/common/send-help-request');
    	$response->assertStatus(200);
    }

    public function testCommonChangeEmail(): void
    {
        $response = $this->get('/common/change-email');
    	$response->assertStatus(200);
    }

    public function testCommonChangePassword(): void
    {
        $response = $this->get('/common/change-password');
    	$response->assertStatus(200);
    }

    public function testCommonTransactionsExportCsv(): void
    {
        $response = $this->get('/common/transactions/export-csv');
    	$response->assertStatus(200);
    }
}
