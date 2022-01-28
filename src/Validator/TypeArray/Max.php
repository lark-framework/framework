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
 * Max rule
 *
 * @author Shay Anderson
 */
class Max extends \Lark\Validator\Rule
{
	/**
	 * Max
	 *
	 * @var integer
	 */
	private int $max;

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'array values cannot exceed maximum value of %s';

	/**
	 * Init
	 *
	 * @param integer $max
	 */
	public function __construct(int $max)
	{
		$this->max = $max;
	}

	/**
	 * Message getter
	 *
	 * @return string
	 */
	public function getMessage(): string
	{
		return sprintf($this->message, $this->max);
	}

	/**
	 * Validate
	 *
	 * @param array $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return max($value) <= $this->max;
	}
}
