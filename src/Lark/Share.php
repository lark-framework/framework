<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark;

/**
 * Share
 *
 * @author Shay Anderson
 */
class Share extends \Lark\Factory\Singleton
{
	/**
	 * Map
	 *
	 * @var \Lark\Map
	 */
	private static Map $map;

	/**
	 * Init
	 */
	protected function __init()
	{
		self::$map = new Map;
	}

	/**
	 * Clear
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear(string $key): void
	{
		self::$map->clear($key);
	}

	/**
	 * Getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		return self::$map->get($key);
	}

	/**
	 * Check if key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return self::$map->has($key);
	}

	/**
	 * Setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set(string $key, $value): void
	{
		self::$map->set($key, $value);
	}

	/**
	 * To array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return self::$map->toArray();
	}
}
