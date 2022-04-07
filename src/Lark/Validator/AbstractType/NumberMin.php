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
 * Abstract number min rule
 *
 * @author Shay Anderson
 */
class NumberMin extends \Lark\Validator\Rule
{
	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must be a minimum value of %s';

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
	 */
	public function __construct($min)
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
	 * @param mixed $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return $value >= $this->min;
	}
}
