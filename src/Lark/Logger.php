<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark;

use Lark\Logger\Record;

/**
 * Logger
 *
 * @author Shay Anderson
 */
class Logger
{
	/**
	 * Log levels
	 */
	const LEVEL_DEBUG = 1;
	const LEVEL_INFO = 2;
	const LEVEL_WARNING = 3;
	const LEVEL_ERROR = 4;
	const LEVEL_CRITICAL = 5;

	/**
	 * Channel
	 *
	 * @var string
	 */
	private string $channel;

	/**
	 * Global context
	 *
	 * @var array
	 */
	private static array $context = [];

	/**
	 * Registered handlers
	 *
	 * @var array
	 */
	private static array $handlers = [];

	/**
	 * Level name map
	 *
	 * @var array
	 */
	private static $levels = [
		self::LEVEL_DEBUG => 'DEBUG',
		self::LEVEL_INFO => 'INFO',
		self::LEVEL_WARNING => 'WARNING',
		self::LEVEL_ERROR => 'ERROR',
		self::LEVEL_CRITICAL => 'CRITICAL'
	];

	/**
	 * Init
	 *
	 * @param string $channel
	 */
	public function __construct(string $channel = '')
	{
		$this->channel = $channel;
	}

	/**
	 * Log critical
	 *
	 * @param string|null $message
	 * @param mixed $context
	 * @return void
	 */
	public function critical(?string $message, $context = null): void
	{
		$this->record(self::LEVEL_CRITICAL, $message, $context);
	}

	/**
	 * Log debug
	 *
	 * @param mixed $message
	 * @param mixed $context
	 * @param int $backtraceKey
	 * @return void
	 */
	public function debug($message = null, $context = null, int $backtraceKey = 1): void
	{
		if ($message !== null && !is_scalar($message)) // message as context
		{
			$context = $message;
			$message = null;
		}

		if (!$message)
		{
			$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
			if (isset($bt[$backtraceKey]['class'], $bt[$backtraceKey]['function']))
			{
				$message = $bt[$backtraceKey]['class'] . '::' . $bt[$backtraceKey]['function'];
			}
		}

		$this->record(self::LEVEL_DEBUG, $message, $context);
	}

	/**
	 * Log error
	 *
	 * @param string|null $message
	 * @param mixed $context
	 * @return void
	 */
	public function error(?string $message, $context = null): void
	{
		$this->record(self::LEVEL_ERROR, $message, $context);
	}

	/**
	 * Level name getter
	 *
	 * @param int $level
	 * @return string
	 * @throws Exception (invalid level)
	 */
	public static function getLevelName(int $level): string
	{
		if (!isset(self::$levels[$level]))
		{
			throw new Exception('Invalid level: ' . $level);
		}

		return self::$levels[$level];
	}

	/**
	 * Add context to global context
	 *
	 * @param array $context
	 * @return void
	 * @throws \Lark\Exception (global context key already exists)
	 */
	public static function globalContext(array $context): void
	{
		if (static::$context === null)
		{
			static::$context = []; // init
		}

		foreach ($context as $k => $v)
		{
			if (isset(static::$context[$k]))
			{
				throw new Exception('Global context key "' . $k . '" already exists');
			}

			static::$context[$k] = $v;
		}
	}

	/**
	 * Register handler
	 *
	 * @param \Lark\Logger\HandlerInterface $handler
	 * @return void
	 */
	public static function handler(\Lark\Logger\HandlerInterface $handler): void
	{
		self::$handlers[] = $handler;
	}

	/**
	 * Log info
	 *
	 * @param string|null $message
	 * @param mixed $context
	 * @return void
	 */
	public function info(?string $message, $context = null): void
	{
		$this->record(self::LEVEL_INFO, $message, $context);
	}

	/**
	 * Send log record to handlers
	 *
	 * @param int $level
	 * @param string|null $message
	 * @param mixed $context
	 * @return void
	 */
	private function record(int $level, ?string $message, $context = null): void
	{
		if (static::$context)
		{
			$context = $context ? (array)$context + static::$context : static::$context;
		}

		$record = new Record($level, self::getLevelName($level), $message, $context, $this->channel);

		/* @var $handler \Lark\Logger\Handler */
		foreach (self::$handlers as $handler)
		{
			if ($handler->isHandling($record) && $handler->write($record) === true) // interrupt
			{
				break;
			}
		}
	}

	/**
	 * Log warning
	 *
	 * @param string|null $message
	 * @param mixed $context
	 * @return void
	 */
	public function warning(?string $message, $context = null): void
	{
		$this->record(self::LEVEL_WARNING, $message, $context);
	}
}
