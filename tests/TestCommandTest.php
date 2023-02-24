<?php

namespace Line\Tests;

use Line\Line;
use Line\Tests\Mocks\LineClient;

class TestCommandTest extends TestCase
{
    /** @test */
    public function it_detects_if_the_login_key_is_set()
    {
        $this->app['config']['line.login_key'] = '';

        $this->artisan('line:test')
            ->expectsOutput('✗ [Line] Could not find your login key, set this in your .env')
            ->assertExitCode(0);

        $this->app['config']['line.login_key'] = 'test';

        $this->artisan('line:test')
            ->expectsOutput('✓ [Line] Found login key')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_if_the_project_key_is_set()
    {
        $this->app['config']['line.project_key'] = '';

        $this->artisan('line:test')
            ->expectsOutput('✗ [Line] Could not find your project key, set this in your .env')
            ->assertExitCode(0);

        $this->app['config']['line.project_key'] = 'test';

        $this->artisan('line:test')
            ->expectsOutput('✓ [Line] Found project key')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_that_its_running_in_the_correct_environment()
    {
        $this->app['config']['app.env'] = 'production';
        $this->app['config']['line.environments'] = [];

        $this->artisan('line:test')
            ->expectsOutput('✗ [Line] Environment (production) not allowed to send errors to Line, set this in your config')
            ->assertExitCode(0);

        $this->app['config']['line.environments'] = ['production'];

        $this->artisan('line:test')
            ->expectsOutput('✓ [Line] Correct environment found (' . config('app.env') . ')')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_detects_that_it_fails_to_send_to_line()
    {
        $this->artisan('line:test')
            ->expectsOutput('✗ [Line] Failed to send exception to Line')
            ->assertExitCode(0);

        $this->app['config']['line.environments'] = [
            'testing',
        ];
        $this->app['line'] = new Line($this->client = new LineClient(
            'login_key',
            'project_key'
        ));

        $this->artisan('line:test')
            ->expectsOutput('✓ [Line] Sent exception to Line with ID: '.LineClient::RESPONSE_ID)
            ->assertExitCode(0);

        $this->assertEquals(LineClient::RESPONSE_ID, $this->app['line']->getLastExceptionId());
    }
}
