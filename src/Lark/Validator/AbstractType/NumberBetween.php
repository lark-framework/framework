<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Validator\AbstractType;

/**
 * Abstract number between rule
 *
 * @author Shay Anderson
 */
class NumberBetween extends \Lark\Validator\Rule
{
	/**
	 * Max
	 *
	 * @var mixed
	 */
	private $max;

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must be between both values';

	/**
	 * Min
	 *
	 * @var mixed
	 */
	private $min;

	/**
	 * Init
	 *
	 * @param mixed $min
	 * @param mixed $max
	 */
	public function __construct($min, $max)
	{
		$this->min = $min;
		$this->max = $max;
	}

	/**
	 * Validate
	 *
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return $value >= $this->min && $value <= $this->max;
	}
}
