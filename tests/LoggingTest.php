<?php

namespace Line\Tests;

class LoggingTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        \Line\Facade::fake();

        $this->app['config']['logging.channels.line'] = ['driver' => 'line'];
        $this->app['config']['logging.default'] = 'line';
        $this->app['config']['line.environments'] = ['testing'];
    }

    /** @test */
    public function it_will_not_send_log_information_to_line()
    {
        $this->app['router']->get('/log-information-via-route/{type}', function (string $type) {
            \Illuminate\Support\Facades\Log::{$type}('log');
        });

        $this->get('/log-information-via-route/debug');
        $this->get('/log-information-via-route/info');
        $this->get('/log-information-via-route/notice');
        $this->get('/log-information-via-route/warning');
        $this->get('/log-information-via-route/error');
        $this->get('/log-information-via-route/critical');
        $this->get('/log-information-via-route/alert');
        $this->get('/log-information-via-route/emergency');

        \Line\Facade::assertRequestsSent(0);
    }

    /** @test */
    public function it_will_only_send_throwables_to_line()
    {
        $this->app['router']->get('/throwables-via-route', function () {
            throw new \Exception('exception-via-route');
        });

        $this->get('/throwables-via-route');

        \Line\Facade::assertRequestsSent(1);
    }
}
