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

use Lark\Map\Path as MapPath;

/**
 * Abstract binding
 *
 * @author Shay Anderson
 */
class Binding
{
	/**
	 * Cache
	 *
	 * @var array
	 */
	private static array $cache = [];

	/**
	 * Allowed map
	 *
	 * @var array
	 */
	private static array $map = [
		'store' => ['db'],
		'validator' => ['rule']
	];

	/**
	 * Getter
	 *
	 * @param string $path
	 * @return void
	 */
	public static function get(string $path)
	{
		return MapPath::get(self::$cache, $path);
	}

	/**
	 * Setter
	 *
	 * @param string $path
	 * @param mixed $args
	 * @return void
	 */
	public static function set(string $path, $args): void
	{
		$pathArr = explode('.', $path);

		if (!isset($pathArr[0]) || !($class = $pathArr[0]) || !isset(self::$map[$class]))
		{
			throw new Exception('Invalid bind path class', [
				'path' => $path,
				'class' => $class ?: null
			]);
		}

		if (!isset($pathArr[1]))
		{
			throw new Exception('Invalid bind path class attribute', [
				'path' => $path,
				'class' => $class
			]);
		}

		$attr = $pathArr[1];

		if (!in_array($attr, self::$map[$class]))
		{
			throw new Exception('Invalid bind path class attribute, class attribute does not exist', [
				'path' => $path,
				'class' => $class,
				'attribute' => $attr
			]);
		}

		MapPath::set(self::$cache, $path, $args);
	}

	/**
	 * Cache getter
	 *
	 * @return array
	 */
	public static function toArray(): array
	{
		return self::$cache;
	}
}
