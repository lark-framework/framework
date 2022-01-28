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

use Lark\Filter;
use Lark\Logger;

/**
 * Logger handler
 *
 * @author Shay Anderson
 */
abstract class Handler implements HandlerInterface
{
	/**
	 * Channel filter
	 *
	 * @var array|null
	 */
	protected ?array $channelFilter;

	/**
	 * Logger level
	 *
	 * @var int
	 */
	protected int $level;

	/**
	 * Init
	 *
	 * @param int $level
	 * @param array $channelFilter
	 */
	public function __construct(int $level = Logger::LEVEL_DEBUG, array $channelFilter = null)
	{
		$this->level = $level;
		$this->channelFilter = $channelFilter;
	}

	/**
	 * Check if handling record
	 *
	 * @param \Lark\Logger\Record $record
	 * @return bool
	 */
	public function isHandling(Record $record): bool
	{
		if (
			$this->channelFilter
			&& !Filter::getInstance()->keys([$record->channel => null], $this->channelFilter)
		)
		{
			return false;
		}

		return $record->level >= $this->level;
	}
}
