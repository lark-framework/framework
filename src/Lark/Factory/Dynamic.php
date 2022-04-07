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
use ReflectionClass;

/**
 * Dynamic factory
 *
 * @author Shay Anderson
 */
class Dynamic
{
	/**
	 * Class name
	 *
	 * @var string
	 */
	private string $class;

	/**
	 * Namespace
	 *
	 * @var string
	 */
	private string $namespace;

	/**
	 * Init
	 *
	 * @param string $namespace
	 * @param string $class
	 */
	public function __construct(string $namespace, string $class)
	{
		$this->namespace = $namespace ? trim($namespace, '\\') . '\\' : '';
		$this->class = $class;
	}

	/**
	 * Class with namespace getter
	 *
	 * @return string
	 */
	public function getClass(): string
	{
		return "{$this->namespace}{$this->class}";
	}

	/**
	 * New instance
	 *
	 * @param mixed ...$args
	 * @return object
	 */
	public function new(...$args): object
	{
		return self::reflectionObject($this->getClass(), $args);
	}

	/**
	 * New instance with array of args
	 *
	 * @param array $args
	 * @return object
	 */
	public function newArgs(array $args): object
	{
		return self::reflectionObject($this->getClass(), $args);
	}

	/**
	 * Reflection object getter
	 *
	 * @param string $class
	 * @param array $args
	 * @return object
	 */
	private static function reflectionObject(string $class, array $args): object
	{
		try
		{
			return (new ReflectionClass($class))->newInstanceArgs($args);
		}
		catch (\ReflectionException $ex)
		{
			throw new Exception($ex->getMessage(), [
				'class' => $class
			]);
		}
	}
}
