<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Request;

/**
 * Request query
 *
 * @author Shay Anderson
 */
class Query extends AbstractInput
{
	/**
	 * Input type
	 */
	const TYPE = INPUT_GET;

	/**
	 * Input array getter
	 *
	 * @return array
	 */
	protected static function &getInputArray(): array
	{
		return $_GET;
	}
}
