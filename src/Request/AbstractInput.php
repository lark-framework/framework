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

/**
 * Abstract request
 *
 * @author Shay Anderson
 */
abstract class AbstractInput
{
	/**
	 * Input type
	 */
	const TYPE = -1;

	/**
	 * Default value
	 *
	 * @var mixed
	 */
	private $default;

	/**
	 * Input key
	 *
	 * @var string
	 */
	protected string $key;

	/**
	 * Init
	 *
	 * @param string $key
	 * @param mixed $default
	 */
	public function __construct(string $key, $default = null)
	{
		$this->key = $key;
		$this->default = $default;
	}

	/**
	 * Default value setter
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function default($value)
	{
		if (!$this->has() || $value === null || $value === '')
		{
			return $this->default;
		}

		return $value;
	}

	/**
	 * Value getter with sanitize filter for email
	 *
	 * @param array $options
	 * @return string
	 */
	public function email(array $options = []): string
	{
		return $this->filter(FILTER_SANITIZE_EMAIL, $options);
	}

	/**
	 * Apply filter
	 *
	 * @param int $filter
	 * @param array $options
	 * @return mixed
	 */
	protected function filter(int $filter, array $options)
	{
		return $this->default(
			filter_input(
				static::TYPE,
				$this->key,
				$filter,
				$options
			)
		);
	}

	/**
	 * Value getter with sanitize filter for float
	 *
	 * @param array $options
	 * @return float
	 */
	public function float(array $options = ['flags' => FILTER_FLAG_ALLOW_FRACTION]): float
	{
		return (float)$this->filter(FILTER_SANITIZE_NUMBER_FLOAT, $options);
	}

	/**
	 * Input array getter
	 *
	 * @return array
	 */
	abstract protected static function &getInputArray(): array;

	/**
	 * Check if key exists
	 *
	 * @return bool
	 */
	public function has(): bool
	{
		return isset(
			static::getInputArray()[$this->key]
		);
	}

	/**
	 * Value getter with sanitize filter for integer
	 *
	 * @param array $options
	 * @return int
	 */
	public function integer(array $options = []): int
	{
		return (int)$this->filter(FILTER_SANITIZE_NUMBER_INT, $options);
	}

	/**
	 * Value getter with sanitize filter for string
	 *
	 * @param array $options
	 * @return string
	 */
	public function string(
		array $options = ['flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
	): string
	{
		return $this->filter(FILTER_SANITIZE_STRING, $options);
	}

	/**
	 * Value getter with sanitize filter for URL
	 *
	 * @param array $options
	 * @return string
	 */
	public function url(array $options = []): string
	{
		return $this->filter(FILTER_SANITIZE_URL, $options);
	}
}
