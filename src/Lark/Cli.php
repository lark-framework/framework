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

use Closure;
use Lark\Cli\CliException;
use Lark\Cli\Command;
use Lark\Cli\Output;
use stdClass;

/**
 * CLI app
 *
 * @author Shay Anderson
 */
class Cli extends \Lark\Factory\Singleton
{
	/**
	 * Command aliases
	 *
	 * @var array
	 */
	private array $aliases = [];

	/**
	 * Raw argv
	 *
	 * @var array
	 */
	private array $argv = [];

	/**
	 * Commands
	 *
	 * @var array
	 */
	private array $commands = [];

	/**
	 * Header callback
	 *
	 * @var Closure|null
	 */
	private ?Closure $headerCallback = null;

	/**
	 * Global options
	 *
	 * @return void
	 */
	private array $options = ['.map' => []];

	/**
	 * Register command
	 *
	 * @param string $name
	 * @param string $description
	 * @param ?string $options
	 * @return \Lark\Cli\Command
	 */
	public function &command(string $name, string $description, array $aliases = []): Command
	{
		// check if already registered
		if (isset($this->commands[$name]))
		{
			throw new CliException("CLI application command \"{$name}\" already exists", [
				'command' => $name,
				'description' => $description
			]);
		}

		$this->commands[$name] = new Command($description);

		foreach ($aliases as $alias)
		{
			if (isset($this->commands[$alias]))
			{
				throw new CliException("CLI application command alias \"{$alias}\" already exists"
					. " as command", [
					'command' => $name,
					'alias' => $alias
				]);
			}

			if (isset($this->aliases[$alias]))
			{
				throw new CliException("CLI application command alias \"{$alias}\" already exists", [
					'command' => $name,
					'alias' => $alias
				]);
			}

			$this->aliases[$alias] = $name;
		}

		return $this->commands[$name];
	}

	/**
	 * Exit with status
	 *
	 * @param integer $status
	 * @return void
	 */
	public function exit($status = 0): void
	{
		exit($status);
	}

	/**
	 * Register header callback
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function header(callable $callback): void
	{
		$this->headerCallback = $callback;
	}

	/**
	 * Print help
	 *
	 * @return void
	 */
	public function help(): void
	{
		if ($this->headerCallback)
		{
			($this->headerCallback)($this->output());
		}

		$this->helpUsage();

		if ($this->options)
		{
			$opts = [];
			foreach ($this->options as $k => $opt)
			{
				if ($k === '.map')
				{
					continue;
				}

				$opts[] = [
					'opt' => (count($opt['options']) > 1
						|| strpos($opt['options'][0], '--') === false ? '' : '    ')
						. implode(', ', $opt['options']),
					'descr' => $opt['description']
				];
			}

			if ($opts)
			{
				// sort options
				usort($opts, function ($a, $b)
				{
					return ltrim($a['opt'], '- ') <=> ltrim($b['opt'], '- ');
				});

				$this->output()->styleBold->echo('Options:');
				$this->output()->grid($opts, [
					'indent' => 2,
					'style' => [
						'descr' => 'colorLightGray',
						'options' => 'styleDim'
					]
				]);
				$this->output()->echo();
			}
		}

		$this->output()->styleBold->echo('Commands:');
		$commands = [];
		foreach ($this->commands as $name => $command)
		{
			$commands[] = [
				'command' => $name,
				'descr' => $command->getDescription()
			];
		}

		// sort commands
		usort($commands, function ($a, $b)
		{
			return $a['command'] <=> $b['command'];
		});

		$this->output()->grid($commands, [
			'indent' => 2,
			'style' => [
				'descr' => 'colorLightGray'
			]
		]);
		$this->output()->echo();

		$this->output()->styleDim
			->echo("Run 'help COMMAND' for command specific help");

		$this->output()->echo();

		$this->exit();
	}

	/**
	 * Print help for command
	 *
	 * @param string $command
	 * @return void
	 */
	private function helpCommand(string $command): void
	{
		$this->requireCommand($command);

		$this->helpUsage(true);

		$fn_options = function (&$list, $key, array $options)
		{
			$opts = [];
			foreach ($options as $idx => $val)
			{
				$opts[] = is_int($idx) ? $val : "{$idx}: \"{$val}\"";
			}

			if ($opts)
			{
				sort($opts);
				$list[$key]['options'] = '(' . implode(', ', $opts) . ')';
			}
		};

		$command = $this->commands[$command];

		$this->output()->echo($command->getDescription());
		$this->output()->echo();

		// display options
		$opts = [];
		foreach ($command->getOptions() as $k => $opt)
		{
			$options = [];
			if (($optionShort = $opt->getOptionShort()))
			{
				$options[] = $optionShort;
			}
			if (($option = $opt->getOption()))
			{
				$options[] = $option;
			}

			$opts[$k] = [
				'opt' => (count($options) > 1
					|| strpos($options[0], '--') === false ? '' : '    ') . implode(', ', $options),
				'descr' => $opt->getDescription(),
				'options' => null
			];

			$fn_options($opts, $k, $opt->getOptions());
		}

		if (count($opts))
		{
			// sort options
			usort($opts, function ($a, $b)
			{
				return ltrim($a['opt'], '- ') <=> ltrim($b['opt'], '- ');
			});

			$this->output()->styleBold->echo('Options:');
			$this->output()->grid($opts, [
				'indent' => 2,
				'style' => [
					'descr' => 'colorLightGray',
					'options' => 'styleDim'
				]
			]);
			$this->output()->echo();
		}


		// display args
		$args = [];
		foreach ($command->getArgs() as $k => $arg)
		{
			/** @var \Lark\Cli\Argument $arg */
			$args[$k] = [
				'arg' => $arg->getName(),
				'descr' => $arg->getDescription(),
				'options' => null
			];

			$fn_options($args, $k, $arg->getOptions());
		}

		if (count($args))
		{

			$this->output()->styleBold->echo('Arguments:');
			$this->output()->grid($args, [
				'indent' => 2,
				'style' => [
					'descr' => 'colorLightGray',
					'options' => 'styleDim'
				]
			]);
			$this->output()->echo();
		}

		$this->exit();
	}

	/**
	 * Display help usage
	 *
	 * @return void
	 */
	private function helpUsage(bool $isCommand = false): void
	{
		$this->output()->echo();
		$file = $this->argv[0] ?? null;
		if ($file)
		{
			$file .= ' ';
		}

		$this->output()->styleBold->echo('Usage: ', '');
		$this->output()->echo("{$file}[OPTIONS] COMMAND" . ($isCommand ? ' [ARGUMENTS]' : ''));
		$this->output()->echo();
	}

	/**
	 * Add global option
	 *
	 * @param string $option
	 * @param string $description
	 * @param callable $action
	 * @return void
	 */
	public function option(string $option, string $description, callable $action): void
	{
		$opt = Cli\Option::parse($option);
		$name = $opt['name'];

		if (isset($this->options[$name]))
		{
			throw new CliException("Global option \"{$option}\" has already been registered");
		}

		if (isset($opt['option']))
		{
			$this->options['.map'][$opt['option']] = $name;
		}

		if (isset($opt['short']))
		{
			$this->options['.map'][$opt['short']] = $name;
		}

		$options = [];
		if ($opt['short'])
		{
			$options[] = $opt['short'];
		}
		if ($opt['option'])
		{
			$options[] = $opt['option'];
		}

		$this->options[$name] = [
			'action' => $action,
			'description' => $description,
			'options' => $options
		];
	}

	/**
	 * Output object getter
	 *
	 * @return Output
	 */
	public function output(): Output
	{
		return new Output;
	}

	/**
	 * Argv parser
	 *
	 * @param array $argv
	 * @return stdClass
	 */
	private function parseArgv(array $argv): stdClass
	{
		$parsed = (object)['name' => null, 'opts' => [], 'args' => []];

		foreach ($argv as $v)
		{
			// opt
			if ($v[0] === '-')
			{
				// match multiple shorts "-abc"
				if (preg_match('/^\-([a-zA-Z]{2,})$/', $v, $m))
				{
					$opt = array_map(function ($v)
					{
						return '-' . $v;
					}, str_split($m[1]));
				}
				// "-x" or "--x"
				else
				{
					$opt = [$v];
				}

				foreach ($opt as $o)
				{
					// global option
					if (isset($this->options['.map'][$o]))
					{
						// invoke
						($this->options[$this->options['.map'][$o]])['action']();
						continue;
					}

					// unique only
					if (!in_array($o, $parsed->opts))
					{
						$parsed->opts[] = $o;
					}
				}
			}
			// command name
			else if (!$parsed->name)
			{
				$parsed->name = $v;
			}
			// arg
			else
			{
				$parsed->args[] = $v;
			}
		}

		return $parsed;
	}

	/**
	 * Throw exception if command does not exist
	 *
	 * @param string $name
	 * @return void
	 */
	private function requireCommand(string $name): void
	{
		if (!isset($this->commands[$name]))
		{
			throw new CliException("Command \"{$name}\" does not exist");
		}
	}

	/**
	 * Run CLI app
	 *
	 * @param array $argv
	 * @return void
	 */
	public function run(array $argv): void
	{
		$this->argv = $argv;
		array_shift($argv); // file

		if (!count($argv))
		{
			$this->help();
		}

		$run = $this->parseArgv($argv);

		if (!$run->name)
		{
			$this->help();
		}

		// check for alias
		if (!isset($this->commands[$run->name]) && isset($this->aliases[$run->name]))
		{
			$run->name = $this->aliases[$run->name];
		}

		$this->requireCommand($run->name);

		/** @var \Lark\Cli\Command $command */
		$command = $this->commands[$run->name];

		// params (args/options) getter
		$params = $command->parse($run->args, $run->opts);

		// invoke action
		$action = &$command->getAction();

		if ($action instanceof Closure)
		{
			call_user_func_array($action, $params);
		}
		else if (is_array($action))
		{
			if (!isset($action[0]) || !isset($action[1]))
			{
				throw new CliException('Invalid command action, class or method does not exist');
			}

			call_user_func_array([new $action[0], $action[1]], $params);
		}
		else
		{
			throw new CliException('Invalid command action, action does not exist');
		}
	}
}
