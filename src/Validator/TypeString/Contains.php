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
 * Contains rule
 *
 * @author Shay Anderson
 */
class Contains extends \Lark\Validator\Rule
{
	/**
	 * Contains value
	 *
	 * @var string
	 */
	private string $containsValue;

	/**
	 * Case insensitive
	 *
	 * @var boolean
	 */
	private bool $isCaseInsensitive;

	/**
	 * Message
	 *
	 * @var string
	 */
	protected string $message = 'must contain value "%s"';

	/**
	 * Init
	 *
	 * @param string $containsValue
	 * @param boolean $caseInsensitive
	 */
	public function __construct(string $containsValue, bool $caseInsensitive = false)
	{
		$this->containsValue = $containsValue;
		$this->isCaseInsensitive = $caseInsensitive;
	}

	/**
	 * Message getter
	 *
	 * @return string
	 */
	public function getMessage(): string
	{
		return sprintf($this->message, $this->containsValue);
	}

	/**
	 * Validate
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return !$this->isCaseInsensitive
			? strpos((string)$value, $this->containsValue) !== false
			: stripos((string)$value, $this->containsValue) !== false;
	}
}
