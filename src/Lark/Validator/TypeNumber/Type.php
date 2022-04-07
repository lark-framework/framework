<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Validator\TypeNumber;

/**
 * Type rule
 *
 * @author Shay Anderson
 */
class Type extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must be a number or null';

	/**
	 * Validate
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		if ($value === null)
		{
			return true;
		}

		// int, float or "int"
		if ($value === null || is_int($value) || is_float($value) || ctype_digit($value))
		{
			return true;
		}

		// "float" but no alpha
		if (is_numeric($value) && !preg_match('/[a-zA-Z]/', $value))
		{
			return true;
		}

		return false;
	}
}
