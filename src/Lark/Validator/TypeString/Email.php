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
 * Email rule
 *
 * @author Shay Anderson
 */
class Email extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must be a valid email address';

	/**
	 * Validate
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
	}
}
