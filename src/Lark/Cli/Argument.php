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
 * CLI command argument
 *
 * @author Shay Anderson
 */
class Argument extends AbstractParameter
{
	/**
	 * Is array flag
	 *
	 * @var boolean
	 */
	private bool $isArray;

	/**
	 * Init
	 *
	 * @param string $arg
	 * @param string $description
	 * @param array $options
	 */
	public function __construct(string $arg, string $description, array $options)
	{
		$this->name = trim($arg);
		$this->description = $description;

		$this->isArray = in_array('array', $options);
		$this->isRequired = !in_array('optional', $options);

		$this->hasDefault = array_key_exists('default', $options);
		if ($this->hasDefault)
		{
			if ($this->isArray() && !is_array($options['default']))
			{
				throw new CliException("Argument \"{$this->name}\" default value must be an array");
			}

			$this->default = $options['default'];
		}

		$this->options = $options;
	}

	/**
	 * Check if is array
	 *
	 * @return boolean
	 */
	public function isArray(): bool
	{
		return $this->isArray;
	}
}
