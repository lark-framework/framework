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
 * Model schema
 *
 * @author Shay Anderson
 */
class Schema
{
	/**
	 * Default values
	 *
	 * @var array
	 */
	private array $defaults = [];

	/**
	 * Model name
	 *
	 * @var string|null
	 */
	private ?string $model = null;

	/**
	 * Schema
	 *
	 * @var array
	 */
	private array $schema;

	/**
	 * Init
	 *
	 * @param array $schema
	 */
	public function __construct(array $schema)
	{
		// detect special fields $[field]
		foreach ($schema as $field => $rules)
		{
			if ($field[0] === '$')
			{
				switch ($field)
				{
					case '$model':
						$this->model = $rules;
						unset($schema[$field]);
						break;

					default:
						throw new Exception('Schema field names cannot start with "$"');
						break;
				}
			}
		}

		$this->schema = $schema;

		// extract defaults
		$this->defaults($this->schema);
	}

	/**
	 * Default values setter
	 *
	 * @param array $schema
	 * @param string|null $parent
	 * @return void
	 */
	private function defaults(array $schema, string $parent = null): void
	{
		foreach ($schema as $k => $v)
		{
			if ($k === 'default')
			{
				$this->defaults[$parent] = $v;
			}
			else if (is_array($v)) // default values can be array
			{
				// key cannot be array index or "fields" for nested fields
				$this->defaults($v, $parent . (!is_int($k) && $k !== 'fields'
					? ($parent ? '.' : null) . $k : null));
			}
		}
	}

	/**
	 * Schema getter
	 *
	 * @return array
	 */
	public function &get(): array
	{
		return $this->schema;
	}

	/**
	 * Default value getter
	 *
	 * @param string $path
	 * @return void
	 */
	public function getDefault(string $path)
	{
		return $this->defaults[$path] ?? null;
	}

	/**
	 * Default values getter
	 *
	 * @return array
	 */
	public function getDefaults(): array
	{
		return $this->defaults;
	}

	/**
	 * Model name getter
	 *
	 * @return string|null
	 */
	public function getModel(): ?string
	{
		return $this->model;
	}
}
