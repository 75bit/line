<?php

namespace Line\Tests;

use Exception;
use Carbon\Carbon;
use Line\Line;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Line\Tests\Mocks\LineClient;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LineTest extends TestCase
{
    /** @var Line */
    protected $line;

    /** @var Mocks\LineClient */
    protected $client;

    public function setUp(): void
    {
        parent::setUp();

        $this->line = new Line($this->client = new LineClient(
            'login_key',
            'project_key'
        ));
    }

    /** @test */
    public function is_will_not_crash_if_line_returns_error_bad_response_exception()
    {
        $this->line = new Line($this->client = new \Line\Http\Client(
            'login_key',
            'project_key'
        ));

        //
        $this->app['config']['line.environments'] = ['testing'];

        $this->client->setGuzzleHttpClient(new Client([
            'handler' => MockHandler::createWithMiddleware([
                new Response(500, [], '{}'),
            ]),
        ]));

        $this->assertInstanceOf(get_class(new \stdClass()), $this->line->handle(new Exception('is_will_not_crash_if_line_returns_error_bad_response_exception')));
    }

    /** @test */
    public function is_will_not_crash_if_line_returns_normal_exception()
    {
        $this->line = new Line($this->client = new \Line\Http\Client(
            'login_key',
            'project_key'
        ));

        //
        $this->app['config']['line.environments'] = ['testing'];

        $this->client->setGuzzleHttpClient(new Client([
            'handler' => MockHandler::createWithMiddleware([
                new \Exception(),
            ]),
        ]));

        $this->assertFalse($this->line->handle(new Exception('is_will_not_crash_if_line_returns_normal_exception')));
    }

    /** @test */
    public function it_can_skip_exceptions_based_on_class()
    {
        $this->app['config']['line.except'] = [];

        $this->assertFalse($this->line->isSkipException(NotFoundHttpException::class));

        $this->app['config']['line.except'] = [
            NotFoundHttpException::class,
        ];

        $this->assertTrue($this->line->isSkipException(NotFoundHttpException::class));
    }

    /** @test */
    public function it_can_skip_exceptions_based_on_environment()
    {
        $this->app['config']['line.environments'] = [];

        $this->assertTrue($this->line->isSkipEnvironment());

        $this->app['config']['line.environments'] = ['production'];

        $this->assertTrue($this->line->isSkipEnvironment());

        $this->app['config']['line.environments'] = ['testing'];

        $this->assertFalse($this->line->isSkipEnvironment());
    }

    /** @test */
    public function it_will_return_false_for_sleeping_cache_exception_if_disabled()
    {
        $this->app['config']['line.sleep'] = 0;

        $this->assertFalse($this->line->isSleepingException([]));
    }

    /** @test */
    public function it_can_check_if_is_a_sleeping_cache_exception()
    {
        $data = ['host' => 'localhost', 'method' => 'GET', 'exception' => 'it_can_check_if_is_a_sleeping_cache_exception', 'line' => 2, 'file' => '/tmp/Line/tests/LineTest.php', 'class' => 'Exception'];

        Carbon::setTestNow('2019-10-12 13:30:00');

        $this->assertFalse($this->line->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:30:00');

        $this->line->addExceptionToSleep($data);

        $this->assertTrue($this->line->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:31:00');

        $this->assertTrue($this->line->isSleepingException($data));

        Carbon::setTestNow('2019-10-12 13:31:01');

        $this->assertFalse($this->line->isSleepingException($data));
    }

    /** @test */
    public function it_can_get_formatted_exception_data()
    {
        $data = $this->line->getExceptionData(new Exception(
            'it_can_get_formatted_exception_data'
        ));

        $this->assertSame('testing', $data['environment']);
        $this->assertSame('localhost', $data['host']);
        $this->assertSame('GET', $data['method']);
        $this->assertSame('http://localhost', $data['fullUrl']);
        $this->assertSame('it_can_get_formatted_exception_data', $data['exception']);

        $this->assertCount(13, $data);
    }

    /** @test */
    public function it_filters_the_data_based_on_the_configuration()
    {
        $this->assertContains('*password*', $this->app['config']['line.blacklist']);

        $data = [
            'password' => 'testing',
            'not_password' => 'testing',
            'not_password2' => [
                'password' => 'testing',
            ],
            'not_password_3' => [
                'nah' => [
                    'password' => 'testing',
                ],
            ],
            'Password' => 'testing',
        ];


        $this->assertContains('***', $this->line->filterVariables($data));
    }

    /** @test */
    public function it_can_report_an_exception_to_line()
    {
        $this->app['config']['line.environments'] = ['testing'];

        $this->line->handle(new Exception('it_can_report_an_exception_to_line'));

        $this->client->assertRequestsSent(1);
    }
}
