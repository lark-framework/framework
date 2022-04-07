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
 * Not allowed rule
 *
 * @author Shay Anderson
 */
class NotAllowed extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'value is not allowed';

	/**
	 * Not allowed
	 *
	 * @var array
	 */
	private array $notAllowed;

	/**
	 * Init
	 *
	 * @param mixed ...$notAllowed
	 */
	public function __construct(...$notAllowed)
	{
		$this->notAllowed = $notAllowed;
	}

	/**
	 * Validate
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return !in_array($value, $this->notAllowed);
	}
}
