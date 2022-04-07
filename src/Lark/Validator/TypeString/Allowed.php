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
 * Allowed rule
 *
 * @author Shay Anderson
 */
class Allowed extends \Lark\Validator\Rule
{
	/**
	 * Allowed
	 *
	 * @var array
	 */
	private array $allowed;

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'value must be allowed';

	/**
	 * Init
	 *
	 * @param string ...$allowed
	 */
	public function __construct(...$allowed)
	{
		$this->allowed = $allowed;
	}

	/**
	 * Validate
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return in_array($value, $this->allowed);
	}
}
