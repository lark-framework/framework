<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Cli;

/**
 * CLI app exception
 *
 * @author Shay Anderson
 */
class CliException extends \Lark\Exception
{
	/**
	 * Status code
	 *
	 * @var int
	 */
	protected $code = 1;
}
