<?php

namespace Line\Tests\Fakes;

use Line\Tests\TestCase;
use Line\Facade as LineFacade;

class LineFakeTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        LineFacade::fake();

        $this->app['config']['logging.channels.line'] = ['driver' => 'line'];
        $this->app['config']['logging.default'] = 'line';
        $this->app['config']['line.environments'] = ['testing'];
    }

    /** @test */
    public function it_will_sent_exception_to_line_if_exception_is_thrown()
    {
        $this->app['router']->get('/exception', function () {
            throw new \Exception('Exception');
        });

        $this->get('/exception');

        LineFacade::assertSent(\Exception::class);

        LineFacade::assertSent(\Exception::class, function (\Throwable $throwable) {
            $this->assertSame('Exception', $throwable->getMessage());

            return true;
        });

        LineFacade::assertNotSent(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
    }

    /** @test */
    public function it_will_sent_nothing_to_line_if_no_exceptions_thrown()
    {
        LineFacade::fake();

        $this->app['router']->get('/nothing', function () {
            //
        });

        $this->get('/nothing');

        LineFacade::assertNothingSent();
    }
}
