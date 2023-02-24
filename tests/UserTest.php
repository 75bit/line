<?php

namespace Line\Tests;

use Line\Line;
use Line\Tests\Mocks\LineClient;
use Illuminate\Foundation\Auth\User as AuthUser;

class UserTest extends TestCase
{
    /** @var Mocks\LineClient */
    protected $client;

    /** @var Line */
    protected $line;

    public function setUp(): void
    {
        parent::setUp();

        $this->line = new Line($this->client = new LineClient(
            'login_key',
            'project_key'
        ));
    }

    /** @test */
    public function it_return_custom_user()
    {
        $this->actingAs((new CustomerUser())->forceFill([
            'id' => 1,
            'username' => 'username',
            'password' => 'password',
            'email' => 'email',
        ]));

        $this->assertSame(['id' => 1, 'username' => 'username', 'password' => 'password', 'email' => 'email'], $this->line->getUser());
    }

    /** @test */
    public function it_return_custom_user_with_to_line()
    {
        $this->actingAs((new CustomerUserWithToLine())->forceFill([
            'id' => 1,
            'username' => 'username',
            'password' => 'password',
            'email' => 'email',
        ]));

        $this->assertSame(['username' => 'username', 'email' => 'email'], $this->line->getUser());
    }

    /** @test */
    public function it_returns_nothing_for_ghost()
    {
        $this->assertSame(null, $this->line->getUser());
    }
}

class CustomerUser extends AuthUser
{
    protected $guarded = [];
}

class CustomerUserWithToLine extends CustomerUser implements \Line\Concerns\Lineable
{
    public function toLine()
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
        ];
    }
}
