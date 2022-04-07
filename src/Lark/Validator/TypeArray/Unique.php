<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Validator\TypeArray;

/**
 * Unique rule
 *
 * @author Shay Anderson
 */
class Unique extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'array values must be unique';

	/**
	 * Validate
	 *
	 * @param array $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return $value === array_unique($value);
	}
}
