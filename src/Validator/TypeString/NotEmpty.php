<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Validator\TypeString;

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
	protected string $message = 'must be a non-empty string';

	/**
	 * Validate
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return is_string($value) && !empty($value);
	}
}
