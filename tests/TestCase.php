<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;

use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Date;

use App\User;
use App\Http\Helper;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        Artisan::call('passport:install');

        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->registerPermissions();
        $this->app->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'user']);

        // Create Admin
        $email = 'demo@devxdao.com';
        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User;
            $user->first_name = 'Ledger';
            $user->last_name = 'Leap';
            $user->email = $email;
            $user->email_verified = 1;
            $user->password = Hash::make('AdminTest');
            $user->role = "admin";
            $user->inflation = 10;
            $user->balance = 0;
            $user->save();
        }

        if (!$user->hasRole('admin'))
            $user->assignRole('admin');
    }

    public function getToken() {
        $user = [
            'email' => 'demo@devxdao.com',
            'password' => 'AdminTest',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/login', $user);

        $apiResponse = $response->baseResponse->getData();
        $token = $apiResponse->user->accessTokenAPI;

        return $token;
    }

    public function getUserToken() {
        $user = [
            'email' => 'demouser@devxdao.com',
            'password' => 'UserTest',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/login', $user);

        $apiResponse = $response->baseResponse->getData();
        $token = $apiResponse->user->accessTokenAPI;
        
        return $token;
    }

    public function getAdminToken() {
        $user = [
            'email' => 'demoadmin@devxdao.com',
            'password' => 'AdminTest',
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->json('post', '/api/login', $user);

        $apiResponse = $response->baseResponse->getData();
        $token = $apiResponse->user->accessTokenAPI;
        
        return $token;
    }

    public function addAdmin() {
        $admin = new User;
        $admin->first_name = 'Test';
        $admin->last_name = 'Admin';
        $admin->email = 'demoadmin@devxdao.com';
        $admin->role = 'admin';
        $admin->password = Hash::make('AdminTest');
        $admin->balance = 1000;
        $admin->confirmation_code = 'TestCode';
        $admin->in_fund = 1;
        $admin->email_verified = 1;
        $admin->email_verified_at = Date::now();
        $admin->save();
        $admin->assignRole('admin');

        Helper::addBalance((int) $admin->balance, $admin->in_fund);
        Helper::addTransaction([
            'user_id' => $admin->id,
            'amount' => $admin->balance,
            'action' => 'Initial Balance',
            'balance' => $admin->balance,
        ]);

        return $admin;
    }

    public function addUser() {
        $user = new User;
        $user->first_name = 'Test';
        $user->last_name = 'User';
        $user->email = 'demouser@devxdao.com';
        $user->role = 'user';
        $user->password = Hash::make('UserTest');
        $user->balance = 1000;
        $user->confirmation_code = 'TestCode';
        $user->in_fund = 1;
        $user->email_verified = 1;
        $user->email_verified_at = Date::now();
        $user->save();
        $user->assignRole('user');

        Helper::addBalance((int) $user->balance, $user->in_fund);
        Helper::addTransaction([
            'user_id' => $user->id,
            'amount' => $user->balance,
            'action' => 'Initial Balance',
            'balance' => $user->balance,
        ]);

        return $user;
    }
}
