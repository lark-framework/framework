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
 * Required rule
 *
 * @author Shay Anderson
 */
class NotNull extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must be a number';

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
			return false;
		}

		// int, float or "int"
		if (is_int($value) || is_float($value) || ctype_digit($value))
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
