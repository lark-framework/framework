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

use \Lark\App;

/**
 * Abstract model
 *
 * @author Shay Anderson
 */
abstract class Model
{
	/**
	 * Store string
	 */
	const STORE = null;

	/**
	 * Make entity
	 *
	 * @param array|object $data
	 * @param integer $entityFlags
	 * @return array|object
	 */
	final public function make($data, int $entityFlags = 0)
	{
		return (new Validator(
			$data,
			self::schemaMake(),
			$entityFlags
		))->make();
	}

	/**
	 * Make entity array
	 *
	 * @param array $data
	 * @param integer $entityFlags
	 * @return array
	 */
	final public function &makeArray(array $data, int $entityFlags = 0): array
	{
		foreach ($data as $k => $v)
		{
			$data[$k] = $this->make($v, $entityFlags);
		}

		return $data;
	}

	/**
	 * Schema getter
	 *
	 * @return array
	 */
	abstract public static function schema(): array;

	/**
	 * Make schema
	 *
	 * @return array
	 */
	final private static function &schemaMake(): array
	{
		$schema = static::schema();

		// model name (and collection fallback)
		if (!isset($schema['$model']))
		{
			// rm namespace if exists
			$schema['$model'] = strpos(static::class, '\\') !== false
				? substr(strrchr(static::class, '\\'), 1) : static::class;
		}

		return $schema;
	}

	/**
	 * Store getter
	 *
	 * @return \Lark\StoreInterface
	 */
	public function store(): \Lark\StoreInterface
	{
		if (!static::STORE)
		{
			throw new Exception('Invalid store constant for Model (Model::STORE)');
		}

		return App::getInstance()->store(static::STORE);
	}
}
