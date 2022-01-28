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
 * Abstract number max rule
 *
 * @author Shay Anderson
 */
class NumberMax extends \Lark\Validator\Rule
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
	protected string $message = 'must be a maximum value of %s';

	/**
	 * Init
	 *
	 * @param mixed $max
	 */
	public function __construct($max)
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
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return $value <= $this->max;
	}
}
