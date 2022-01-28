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
 * Alnum rule
 *
 * @author Shay Anderson
 */
class Alnum extends \Lark\Validator\Rule
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
	protected string $message = 'must only contain alphanumeric characters';

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message2 = 'must only contain alphanumeric characters and whitespaces';

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
			? preg_match('/^[a-zA-Z0-9\s]+$/', (string)$value) === 1
			: ctype_alnum((string)$value);
	}
}
