<?php

namespace App\Core\Log;

use Psr\Log\AbstractLogger;
use Stringable;

/**
 * PSR-3 compliant file logger.
 *
 * Extends {@see AbstractLogger}, which maps the eight level helpers
 * (emergency, alert, critical, error, warning, notice, info, debug) onto the
 * single {@see log()} method implemented here — so the class is drop-in
 * swappable with any other PSR-3 logger (e.g. Monolog).
 */
class Logger extends AbstractLogger
{
    /**
     * Absolute path to the log file.
     */
    private string $logFile;

    /**
     * Append handle, opened lazily on first write.
     *
     * @var resource|null
     */
    private $handle = null;

    /**
     * @param string|null $logFile Path to write to; defaults to the configured path.
     */
    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? (string) config('configuration.logging_path');

        if (!file_exists($this->logFile)) {
            $created = @fopen($this->logFile, 'w');

            if ($created === false) {
                throw new \RuntimeException("Unable to create log file: {$this->logFile}");
            }

            fclose($created);
        }

        if (!is_writable($this->logFile)) {
            throw new \RuntimeException("Log file is not writable: {$this->logFile}");
        }
    }

    /**
     * Write a single log entry.
     *
     * @param mixed                $level   One of the Psr\Log\LogLevel constants.
     * @param string|Stringable    $message Message, optionally containing {placeholders}.
     * @param array<string, mixed> $context Values interpolated into the placeholders.
     */
    public function log($level, string|Stringable $message, array $context = []): void
    {
        $message = $this->interpolate((string) $message, $context);

        $path = ($_SERVER['SERVER_NAME'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '');
        $time = date('m-d-Y H:i:s');
        $severity = strtoupper((string) $level);

        fwrite($this->fileHandle(), "[{$time}] [{$path}] : [{$severity}] - {$message}" . PHP_EOL);
    }

    /**
     * Replace PSR-3 {placeholders} with values from the context array.
     *
     * @param array<string, mixed> $context
     */
    private function interpolate(string $message, array $context): string
    {
        $replacements = [];

        foreach ($context as $key => $value) {
            if ($value === null || is_scalar($value) || $value instanceof Stringable) {
                $replacements['{' . $key . '}'] = (string) $value;
            }
        }

        return strtr($message, $replacements);
    }

    /**
     * Lazily open (append mode) and return the file handle.
     *
     * @return resource
     */
    private function fileHandle()
    {
        if (!is_resource($this->handle)) {
            $handle = fopen($this->logFile, 'a');

            if ($handle === false) {
                throw new \RuntimeException("Unable to open log file: {$this->logFile}");
            }

            $this->handle = $handle;
        }

        return $this->handle;
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            fclose($this->handle);
        }
    }
}
