<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Factory;

use Lark\Exception;

/**
 * Singleton factory
 *
 * @author Shay Anderson
 */
abstract class Singleton
{
	/**
	 * Instances
	 *
	 * @var array
	 */
	private static $instances = [];

	/**
	 * Protected
	 */
	final private function __construct()
	{
		if (method_exists($this, '__init'))
		{
			$this->__init();
		}
	}

	/**
	 * Not allowed
	 */
	final private function __clone()
	{
		throw new Exception('This method is not allowed on Singleton', [
			'method' => '__clone'
		]);
	}

	/**
	 * Not allowed
	 */
	final private function __wakeup()
	{
		throw new Exception('This method is not allowed on Singleton', [
			'method' => '__wakeup'
		]);
	}

	/**
	 * Instance getter
	 *
	 * @return \Lark\Factory\Singleton
	 */
	public static function getInstance(): self
	{
		$class = static::class;

		if (!isset(self::$instances[$class]))
		{
			self::$instances[$class] = new static;
		}

		return self::$instances[$class];
	}
}
