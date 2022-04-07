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
 * Filter
 *
 * @author Shay Anderson
 */
class Filter extends \Lark\Factory\Singleton
{
	/**
	 * Sanitize value with email filter
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public function email($value, array $options = []): string
	{
		return $this->filter($value, FILTER_SANITIZE_EMAIL, $options);
	}

	/**
	 * Filter value
	 *
	 * @param mixed $value
	 * @param integer $filter
	 * @param array $options
	 * @return mixed
	 */
	protected function filter($value, int $filter, array $options)
	{
		return filter_var($value, $filter, $options);
	}

	/**
	 * Sanitize value with float filter
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return float
	 */
	public function float($value, array $options = ['flags' => FILTER_FLAG_ALLOW_FRACTION]): float
	{
		return (float)$this->filter($value, FILTER_SANITIZE_NUMBER_FLOAT, $options);
	}

	/**
	 * Sanitize value with integer filter
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return int
	 */
	public function integer($value, array $options = []): int
	{
		return $this->filter($value, FILTER_SANITIZE_NUMBER_INT, $options);
	}

	/**
	 * Filters keys based on include or exclude filter
	 *
	 * @param array $array
	 * @param array $filter (include: [key => 1, ...] or exclude: [key => 0, ...])
	 * @return array
	 */
	public function keys(array $array, array $filter): array
	{
		$isExclude = reset($filter) == 0;

		foreach ($array as $k => $v)
		{
			if ($isExclude)
			{
				if (isset($filter[$k]) && $filter[$k] == 0)
				{
					unset($array[$k]);
				}
			}
			else // include
			{
				if (isset($filter[$k]) && $filter[$k] == 1)
				{
					continue;
				}

				unset($array[$k]);
			}
		}

		return $array;
	}

	/**
	 * Sanitize value with string filter
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public function string(
		$value,
		array $options = ['flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
	): string
	{
		return $this->filter($value, FILTER_SANITIZE_STRING, $options);
	}

	/**
	 * Sanitize value with url filter
	 *
	 * @param mixed $value
	 * @param array $options
	 * @return string
	 */
	public function url($value, array $options = []): string
	{
		return $this->filter($value, FILTER_SANITIZE_URL, $options);
	}
}
