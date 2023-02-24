<?php

namespace Line\Commands;

use Exception;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    protected $signature = 'line:test {exception?}';

    protected $description = 'Generate a test exception and send it to line';

    public function handle()
    {
        try {
            /** @var Line $line */
            $line = app('line');

            if (config('line.login_key')) {
                $this->info('✓ [Line] Found login key');
            } else {
                $this->error('✗ [Line] Could not find your login key, set this in your .env');
            }

            if (config('line.project_key')) {
                $this->info('✓ [Line] Found project key');
            } else {
                $this->error('✗ [Line] Could not find your project key, set this in your .env');
                $this->info('More information on setting your project key: https://www.75line.com/docs/how-to-use/installation');
            }

            if (in_array(config('app.env'), config('line.environments'))) {
                $this->info('✓ [Line] Correct environment found (' . config('app.env') . ')');
            } else {
                $this->error('✗ [Line] Environment (' . config('app.env') . ') not allowed to send errors to Line, set this in your config');
                $this->info('More information about environment configuration: https://www.75line.com/docs/how-to-use/installation');
            }

            $response = $line->handle(
                $this->generateException()
            );

            if (isset($response->id)) {
                $this->info('✓ [Line] Sent exception to Line with ID: '.$response->id);
            } elseif (is_null($response)) {
                $this->info('✓ [Line] Sent exception to Line!');
            } else {
                $this->error('✗ [Line] Failed to send exception to Line');
            }
        } catch (\Exception $ex) {
            $this->error("✗ [Line] {$ex->getMessage()}");
        }
    }

    public function generateException(): ?Exception
    {
        try {
            throw new Exception($this->argument('exception') ?? 'This is a test exception from the Line console');
        } catch (Exception $ex) {
            return $ex;
        }
    }
}
