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
 * Cli app command
 *
 * @author Shay Anderson
 */
class Command
{
	/**
	 * Command action
	 *
	 * @var array|callable
	 */
	private $action;

	/**
	 * Command args
	 *
	 * @var array
	 */
	private array $args = [];

	/**
	 * Description
	 *
	 * @var string
	 */
	private string $description;

	/**
	 * Has arg with array value flag
	 *
	 * @var boolean
	 */
	private bool $hasArgArray = false;

	/**
	 * Command options
	 *
	 * @var array
	 */
	private array $options = [];

	/**
	 * Options map
	 *
	 * @var array
	 */
	private array $optionsMap = [];

	/**
	 * Init
	 *
	 * @param string $description
	 */
	public function __construct(string $description)
	{
		$this->description = $description;
	}

	/**
	 * Action setter
	 *
	 * @param array|callable $callbackOrClassArray
	 * @return self
	 */
	public function action($callbackOrClassArray): self
	{
		$this->action = $callbackOrClassArray;
		return $this;
	}

	/**
	 * Argument setter
	 *
	 * @param string $arg
	 * @param string $description
	 * @param array $options
	 * @return self
	 */
	public function arg(string $arg, string $description, array $options = []): self
	{
		$this->bind(
			new Argument($arg, $description, $options)
		);

		return $this;
	}

	/**
	 * Bind param
	 *
	 * @param AbstractParameter $param
	 * @return void
	 */
	private function bind(AbstractParameter $param): void
	{
		$name = $param->getName();

		if (isset($this->args[$name]) || isset($this->options[$name]))
		{
			throw new CliException("Parameter \"{$name}\" has already been registered");
		}

		if ($param instanceof Argument)
		{
			if ($this->hasArgArray)
			{
				throw new CliException("Argument \"{$name}\" cannot be registered after an array"
					. " argument has been registered, the array argument must be the last argument");
			}

			$this->args[$name] = $param;

			if ($param->isArray())
			{
				$this->hasArgArray = true;
			}
		}
		else
		{
			$option = $param->getOption();
			$optionShort = $param->getOptionShort();

			if ($option)
			{

				if (isset($this->optionsMap[$option]))
				{
					throw new CliException("Option \"{$option}\" has already been registered");
				}
				$this->optionsMap[$option] = $name;
			}

			if ($optionShort)
			{
				if (isset($this->optionMap[$optionShort]))
				{
					throw new CliException("Option \"{$optionShort}\" has already been registered");
				}

				$this->optionsMap[$optionShort] = $name;
			}

			$this->options[$name] = $param;
		}
	}

	/**
	 * Action getter
	 *
	 * @return void
	 */
	public function &getAction()
	{
		return $this->action;
	}

	/**
	 * Args getter
	 *
	 * @return array
	 */
	public function getArgs(): array
	{
		return $this->args;
	}

	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * Description getter
	 *
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * Option setter
	 *
	 * @param string $option
	 * @param string $description
	 * @param array $options
	 * @return self
	 */
	public function option(string $option, string $description, array $options = []): self
	{
		$this->bind(
			new Option($option, $description, $options)
		);

		return $this;
	}

	/**
	 * Params parser
	 *
	 * @param array $arguments
	 * @param array $options
	 * @return array
	 */
	public function parse(array $arguments, array $options): array
	{
		$argObjects = $this->args;
		$argObjLast = null;
		$args = [];

		foreach (array_filter($arguments) as $arg)
		{
			/** @var \Lark\Cli\Argument $argObj */
			$argObj = array_shift($argObjects);

			if (!$argObj && $argObjLast)
			{
				$argObj = &$argObjLast;
			}

			if ($argObj)
			{
				if ($argObj->isArray())
				{
					$argObjLast = $argObj;
					$args[$argObj->getName()][] = $arg;
				}
				else
				{
					$args[$argObj->getName()] = $arg;
				}
			}
		}

		$opts = [];
		foreach (array_filter($options) as $opt)
		{
			// option parts "--option=val"
			$optionParts = explode('=', $opt);
			$option = array_shift($optionParts);
			$value = implode('=', $optionParts);
			$name = $this->optionsMap[$option] ?? null;

			// match option "-o(=x?)" or "--option(=x?)"
			if (($name = $this->optionsMap[$option] ?? null))
			{
				$opts[$name] = count($optionParts) ? $value : true;
				continue;
			}

			throw new CliException("Invalid unregistered option \"{$option}\"");
		}

		// set final args
		$arguments = [];
		foreach ($this->args as $argName => $argument)
		{
			/** @var \Lark\Cli\Argument $argument */
			if (array_key_exists($argName, $args))
			{
				$arguments[$argName] = $args[$argName];
			}
			else if ($argument->hasDefault())
			{
				$arguments[$argName] = $argument->getDefault();
			}
			else
			{
				$arguments[$argName] = $argument->isArray() ? [] : null;
			}

			$invalidValue = false;

			if ($argument->isRequired())
			{
				if ($argument->isArray())
				{
					if (empty($arguments[$argName]))
					{
						$invalidValue = true;
					}
				}
				else if ($arguments[$argName] === null || $arguments[$argName] === '')
				{
					$invalidValue = true;
				}
			}

			if ($invalidValue)
			{
				throw new CliException("Argument \"{$argName}\" is required");
			}
		}

		// set final options
		$options = [];
		foreach ($this->options as $optionName => $option)
		{
			/** @var \Lark\Cli\Option $option */
			if (array_key_exists($optionName, $opts))
			{
				$options[$optionName] = $opts[$optionName];
			}
			else if ($option->hasDefault())
			{
				$options[$optionName] = $option->getDefault();
			}
			else
			{
				$options[$optionName] = null;
			}

			if (
				$option->isRequired()
				&& ($options[$optionName] === '' || $options[$optionName] === null)
			)
			{
				throw new CliException("Option \"{$optionName}\" is required");
			}
		}

		return $arguments + $options;
	}
}
