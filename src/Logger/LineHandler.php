<?php

namespace Line\Logger;

use Throwable;
use Monolog\Logger;
use Line\Line;
use Monolog\Handler\AbstractProcessingHandler;

class LineHandler extends AbstractProcessingHandler
{
    /** @var Line */
    protected $line;

    /**
     * @param Line $line
     * @param int $level
     * @param bool $bubble
     */
    public function __construct(Line $line, $level = Logger::ERROR, bool $bubble = true)
    {
        $this->line = $line;

        parent::__construct($level, $bubble);
    }

    /**
     * @param array $record
     */
    protected function write($record): void
    {
        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
            $this->line->handle(
                $record['context']['exception']
            );

            return;
        }
    }
}
