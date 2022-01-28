<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Request;

use Lark\Map\Path;

/**
 * Request JSON body
 *
 * @author Shay Anderson
 */
class Json extends AbstractInput
{
	/**
	 * JSON body
	 *
	 * @var array
	 */
	private static $arr;

	/**
	 * Apply filter
	 *
	 * @param integer $filter
	 * @param array $options
	 * @return mixed
	 */
	protected function filter(int $filter, array $options)
	{
		return filter_var($this->default(
			// check if map path "fX.fY"
			strpos($this->key, '.') !== false
				? (
					// check if "fX.fY" exists
					Path::has(self::getInputArray(), $this->key)
					? Path::get(self::getInputArray(), $this->key)
					: null
				)
				// field "fX"
				: self::getInputArray()[$this->key] ?? null
		), $filter, $options);
	}

	/**
	 * Input array getter
	 *
	 * @return array
	 */
	protected static function &getInputArray(): array
	{
		if (self::$arr === null)
		{
			self::$arr = \Lark\App::getInstance()->request()->json(true);
		}

		return self::$arr;
	}

	/**
	 * Check if key exists
	 *
	 * @return bool
	 */
	public function has(): bool
	{
		return strpos($this->key, '.') === false
			// field "fX"
			? isset(self::getInputArray()[$this->key])
			// map path "fX.fY"
			: Path::has(self::getInputArray(), $this->key);
	}
}
