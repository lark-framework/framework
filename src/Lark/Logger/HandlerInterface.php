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

/**
 * Logger handler interface
 *
 * @author Shay Anderson
 */
interface HandlerInterface
{
	/**
	 * Close/end logging
	 *
	 * @return mixed
	 */
	public function close();

	/**
	 * Check if handling record
	 *
	 * @param \Lark\Logger\Record $record
	 * @return bool
	 */
	public function isHandling(Record $record): bool;

	/**
	 * Write to log
	 *
	 * @param \Lark\Logger\Record $record
	 * @return void
	 */
	public function write(Record $record): void;
}
