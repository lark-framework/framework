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
	protected string $message = 'length must be a minimum of %s characters';

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
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return mb_strlen((string)$value) >= $this->min;
	}
}
