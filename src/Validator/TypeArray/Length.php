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
 * Length rule
 *
 * @author Shay Anderson
 */
class Length extends \Lark\Validator\Rule
{
	/**
	 * Length
	 *
	 * @var integer
	 */
	private int $length;

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'number of array items must be %s';

	/**
	 * Init
	 *
	 * @param integer $length
	 */
	public function __construct(int $length)
	{
		$this->length = $length;
	}

	/**
	 * Message getter
	 *
	 * @return string
	 */
	public function getMessage(): string
	{
		return sprintf($this->message, $this->length);
	}

	/**
	 * Validate
	 *
	 * @param array $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return count($value) === $this->length;
	}
}
