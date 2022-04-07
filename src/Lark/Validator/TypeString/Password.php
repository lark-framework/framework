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
 * Password rule
 *
 * @author Shay Anderson
 */
class Password extends \Lark\Validator\Rule
{
	/**
	 * Hash
	 *
	 * @var string
	 */
	private string $hash;

	/**
	 * Init
	 *
	 * @var string
	 */
	protected string $message = 'passwords must match';

	/**
	 * Init
	 *
	 * @param string $hash
	 */
	public function __construct(string $hash)
	{
		$this->hash = $hash;
	}

	/**
	 * Validate
	 *
	 * @param string $value
	 * @return boolean
	 */
	public function validate($value): bool
	{
		return password_verify((string)$value, $this->hash);
	}
}
