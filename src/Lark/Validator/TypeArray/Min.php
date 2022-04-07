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
 * Min rule
 *
 * @author Shay Anderson
 */
class Min extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'array values cannot be lower than minimum value of %s';

	/**
	 * Min
	 *
	 * @var integer
	 */
	private int $min;

	/**
	 * Init
	 *
	 * @param integer $min
	 */
	public function __construct(int $min)
	{
		$this->min = $min;
	}

	/**
	 * Message getter
	 *
	 * @return string
	 */
	public function getMessage(): string
	{
		return sprintf($this->message, $this->min);
	}

	/**
	 * Validate
	 *
	 * @param array $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return min($value) >= $this->min;
	}
}
