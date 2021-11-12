<?php

namespace Tests;

use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Hash;

final class CriticalFunctionsTest extends TestCase
{
    // public function testCanBeCreatedFromValidEmailAddress(): void
    // {
    //     $this->assertInstanceOf(
    //         Email::class,
    //         Email::fromString('user@example.com')
    //     );
    // }

    // public function testCannotBeCreatedFromInvalidEmailAddress(): void
    // {
    //     $this->expectException(InvalidArgumentException::class);

    //     Email::fromString('invalid');
    // }

    // public function testCanBeUsedAsString(): void
    // {
    //     $this->assertEquals(
    //         'user@example.com',
    //         Email::fromString('user@example.com')
    //     );
    // }




    public function testresetUserPassword(): void
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $hash = Hash::make('password');
        echo $hash;
    }
}




?>