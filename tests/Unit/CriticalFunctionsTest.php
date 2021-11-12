<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

final class CriticalFunctionsTest extends TestCase
{
    public function testInvitationCode(): void
    {
        $this->get('/invitation/123');
    }

    public function testInvitation(): void
    {
        $this->get('/invitation');
    }

    public function testLogin(): void
    {
        $this->get('/login');
    }

    public function testSendResetEmail(): void
    {
        $this->get('/common/send-reset-email');
    }

    public function testResetPassword(): void
    {
        $this->get('/common/reset-password');
    }

    public function testMe(): void
    {
        $this->get('/me');
    }

    public function testUser(): void
    {
        $this->get('/user');
    }

    public function testUserWithdraw(): void
    {
        $this->get('/user/withdraw');
    }

    public function testUserGraphInfo(): void
    {
        $this->get('/user/graph-info');
    }

    public function testAdminUsers(): void
    {
        $this->get('/admin/users');
    }

    public function testDdminUsersAll(): void
    {
        $this->get('/admin/users/all');
    }

    public function testAdminUser(): void
    {
        $this->get('/admin/user/1');
    }

    public function testAdminValues(): void
    {
        $this->get('/admin/values');
    }

    public function testAdminUserExportCsv(): void
    {
        $this->get('/admin/user/1/export-csv');
    }

    public function testAdminCsprPrice(): void
    {
        $this->get('/admin/cspr-price');
    }

    public function testAdminBalance(): void
    {
        $this->get('/admin/balance');
    }

    public function testAdminWithdraw(): void
    {
        $this->get('/admin/withdraw');
    }

    public function testAdminDeposit(): void
    {
        $this->get('/admin/deposit');
    }

    public function testAdminUserFund(): void
    {
        $this->get('/admin/user/1/fund');
    }

    public function testAdminResetUserAassword(): void
    {
        $this->get('/admin/reset-user-password');
    }

    public function testAdminUsersExportCsv(): void
    {
        $this->get('/admin/users/export-csv');
    }

    public function testAdminFundSale(): void
    {
        $this->get('/admin/fund-sale');
    }

    public function testCommonSettings(): void
    {
        $this->get('/common/settings');
    }

    public function testCommonTransactions(): void
    {
        $this->get('/common/transactions');
    }

    public function testCommonLogs(): void
    {
        $this->get('/common/logs');
    }

    public function testCommonSendHelpRequest(): void
    {
        $this->get('/common/send-help-request');
    }

    public function testCommonChangeEmail(): void
    {
        $this->get('/common/change-email');
    }

    public function testCommonChangePassword(): void
    {
        $this->get('/common/change-password');
    }

    public function testCommonTransactionsExportCsv(): void
    {
        $this->get('/common/transactions/export-csv');
    }

}
