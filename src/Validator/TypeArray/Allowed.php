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
	protected string $message = 'array values must be allowed';

	/**
	 * Init
	 *
	 * @param mixed ...$allowed
	 */
	public function __construct(...$allowed)
	{
		$this->allowed = $allowed;
	}

	/**
	 * Validate
	 *
	 * @param array $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return $value === array_intersect($value, $this->allowed);
	}
}
