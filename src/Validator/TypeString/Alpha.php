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
 * Alpha rule
 *
 * @author Shay Anderson
 */
class Alpha extends \Lark\Validator\Rule
{
	/**
	 * Allow whitespaces
	 *
	 * @var boolean
	 */
	private bool $allowWhitespaces;

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must only contain alphabetic characters';

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message2 = 'must only contain alphabetic characters and whitespaces';

	/**
	 * Init
	 *
	 * @param boolean $allowWhitespaces
	 */
	public function __construct(bool $allowWhitespaces = false)
	{
		$this->allowWhitespaces = $allowWhitespaces;
	}

	/**
	 * Message getter
	 *
	 * @return string
	 */
	public function getMessage(): string
	{
		return $this->allowWhitespaces ? $this->message2 : $this->message;
	}

	/**
	 * Validate
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return $this->allowWhitespaces
			? preg_match('/^[a-zA-Z\s]+$/', (string)$value) === 1
			: ctype_alpha((string)$value);
	}
}
