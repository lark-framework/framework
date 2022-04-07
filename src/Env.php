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
 * Environment variables helper
 *
 * @author Shay Anderson
 */
class Env extends \Lark\Factory\Singleton
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
	 * From array setter
	 *
	 * @param array $array
	 * @return void
	 */
	public function fromArray(array $array): void
	{
		self::$map->merge($array);
	}

	/**
	 * Getter
	 *
	 * @param string $key
	 * @param mixed $default
	 * @param bool $invalidKeyException (throw exception on invalid key)
	 * @return mixed
	 * @throws \Lark\Exception (on invalid key)
	 */
	public function get(string $key, $default = null, bool $invalidKeyException = false)
	{
		if (self::$map->has($key))
		{
			return self::$map->get($key);
		}

		if ($invalidKeyException)
		{
			throw new Exception('Invalid env variable key "' . $key . '"');
		}

		return $default;
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
	 * Load env file
	 *
	 * @param string $path
	 * @return void
	 * @throws \Lark\Exception (on invalid path or load fail)
	 */
	public function load(string $path): void
	{
		if (!is_readable($path))
		{
			throw new Exception('Failed to load env file "' . $path . '"');
		}

		$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		if (!is_array($lines))
		{
			throw new Exception('Failed to load env file');
		}

		$map = [];
		foreach ($lines as $k => $v)
		{
			$v = trim($v);

			if ($v[0] !== '#' && ($pos = strpos($v, '=')) !== false) // no comments
			{
				// trim: var="val" and var='val' => var=val
				self::$map->set(
					substr($v, 0, $pos),
					trim(
						trim(
							substr($v, ++$pos),
							'"'
						),
						'\''
					)
				);
			}
		}

		unset($lines);
	}

	/**
	 * Return all as array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return self::$map->toArray();
	}
}
