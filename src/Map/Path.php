<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Map;

/**
 * Map path helper
 *
 * @author Shay Anderson
 */
class Path
{
	/**
	 * Array path clear
	 *
	 * @param array $array
	 * @param string $key
	 * @param string $separator
	 * @return void
	 */
	public static function clear(array &$array, string $key, string $separator = '.'): void
	{
		if (!self::has($array, $key, $separator))
		{
			return;
		}

		$keys = explode($separator, $key);
		$i = 0;
		$c = count($keys);
		$r = &$array;
		foreach ($keys as $k)
		{
			if (isset($r[$k]))
			{
				$i++;
				if ($i === $c) // correct depth
				{
					unset($r[$k]);
					return;
				}
				$r = &$r[$k];
			}
			else
			{
				return;
			}
		}
	}

	/**
	 * Array path getter
	 *
	 * @param array $array
	 * @param string $key
	 * @param string $separator
	 * @return mixed
	 */
	public static function get(array &$array, string $key, string $separator = '.')
	{
		$r = &$array;
		foreach (explode($separator, $key) as $k)
		{
			if (isset($r[$k]))
			{
				$r = &$r[$k];
			}
			else
			{
				return null;
			}
		}

		return $r;
	}

	/**
	 * Check if array path exists
	 *
	 * @param array $array
	 * @param string $key
	 * @param string $separator
	 * @return bool
	 */
	public static function has(array &$array, string $key, string $separator = '.'): bool
	{
		$r = &$array;
		foreach (explode($separator, $key) as $k)
		{
			if (isset($r[$k]))
			{
				$r = &$r[$k];
			}
			else
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Array path setter
	 *
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 * @param string $separator
	 * @return void
	 */
	public static function set(array &$array, string $key, $value, string $separator = '.'): void
	{
		foreach (explode($separator, $key) as $k)
		{
			$array = &$array[$k];
		}

		$array = $value;
	}
}
