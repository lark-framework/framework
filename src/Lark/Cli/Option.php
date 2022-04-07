<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Cli;

/**
 * CLI command option
 *
 * @author Shay Anderson
 */
class Option extends AbstractParameter
{
	/**
	 * Option
	 *
	 * @var string
	 */
	private string $option;

	/**
	 * Option short
	 *
	 * @var string
	 */
	private string $optionShort;

	/**
	 * Init
	 *
	 * @param string $option
	 * @param string $description
	 * @param array $options
	 */
	public function __construct(string $option, string $description, array $options)
	{
		$parsed = self::parse($option);
		$this->name = $parsed['name'];
		$this->option = $parsed['option'];
		$this->optionShort = $parsed['short'];
		$this->description = $description;
		$this->isRequired = false;
		$this->hasDefault = array_key_exists('default', $options);

		if ($this->hasDefault)
		{
			$this->default = $options['default'];
		}

		$this->options = $options;
	}

	/**
	 * Option getter
	 *
	 * @return string
	 */
	public function getOption(): string
	{
		return $this->option;
	}

	/**
	 * Option short getter
	 *
	 * @return string
	 */
	public function getOptionShort(): string
	{
		return $this->optionShort;
	}

	/**
	 * Parse option
	 *
	 * @param string $option
	 * @return array (name, option, short)
	 */
	public static function parse(string $option): array
	{
		$parsed = ['name' => '', 'option' => '', 'short' => ''];

		// replace comma with space: "-x,--xyz" => "-x --xyz"
		if (strpos($option, ',') !== false)
		{
			$option = str_replace(',', ' ', $option);
		}

		// array of options, split on space " "
		$opts = array_values(
			array_filter(
				explode(' ', $option)
			)
		);

		foreach ($opts as $opt)
		{
			// --x
			if (substr($opt, 0, 2) === '--')
			{
				$parsed['option'] = trim($opt);
			}
			// -x
			else
			{
				$parsed['short'] = trim($opt);
			}
		}

		$parsed['name'] = ltrim($parsed['option'] ? $parsed['option'] : $parsed['short'], '-');

		return $parsed;
	}
}
