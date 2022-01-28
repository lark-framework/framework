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
 * Not empty rule
 *
 * @author Shay Anderson
 */
class NotEmpty extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must be a non-zero number';

	/**
	 * Validate
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		if (empty($value))
		{
			return false;
		}

		// check value like "0.0"
		if ((int)$value == 0 && (float)$value == 0)
		{
			return false;
		}

		return true;
	}
}
