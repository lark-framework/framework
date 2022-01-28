<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Logger;

use Lark\Exception;

/**
 * Logger record
 *
 * @author Shay Anderson
 */
class Record
{
	/**
	 * Channel
	 *
	 * @var string
	 */
	public string $channel;

	/**
	 * Context
	 *
	 * @var array|null
	 */
	public ?array $context;

	/**
	 * Logger level
	 *
	 * @var int
	 */
	public int $level;

	/**
	 * Logger level name
	 *
	 * @var string
	 */
	public string $levelName;

	/**
	 * Message
	 *
	 * @var string|null
	 */
	public ?string $message;

	/**
	 * Unix timestamp
	 *
	 * @var int
	 */
	public int $timestamp;

	/**
	 * Init
	 *
	 * @param int $level
	 * @param string $levelName
	 * @param string|null $message
	 * @param mixed $context
	 * @param string $channel
	 */
	public function __construct(int $level, string $levelName, ?string $message, $context, string $channel)
	{
		if (!$message && !$context && !$channel)
		{
			throw new Exception('Logger record cannot have empty message, context and channel');
		}

		$this->level = $level;
		$this->levelName = $levelName;
		$this->message = $message;
		$this->context = $context;
		if ($channel !== '')
		{
			$this->channel = $channel;
		}
		$this->timestamp = time();
	}
}
