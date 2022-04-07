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
 * CLI output
 *
 * @author Shay Anderson
 *
 * @property self $bgBlack
 * @property self $bgBlue
 * @property self $bgCyan
 * @property self $bgGray
 * @property self $bgGreen
 * @property self $bgPurple
 * @property self $bgRed
 * @property self $bgWhite
 * @property self $bgYellow
 * @property self $bgLigthBlue
 * @property self $bgLightCyan
 * @property self $bgLightGray
 * @property self $bgLightGreen
 * @property self $bgLightPurple
 * @property self $bgLightRed
 * @property self $bgLightYellow
 *
 * @property self $colorBlack
 * @property self $colorBlue
 * @property self $colorCyan
 * @property self $colorGray
 * @property self $colorGreen
 * @property self $colorPurple
 * @property self $colorRed
 * @property self $colorWhite
 * @property self $colorYellow
 * @property self $colorLigthBlue
 * @property self $colorLightCyan
 * @property self $colorLightGray
 * @property self $colorLightGreen
 * @property self $colorLightPurple
 * @property self $colorLightRed
 * @property self $colorLightYellow
 *
 * @property self $styleBlink
 * @property self $styleBold
 * @property self $styleDim
 * @property self $styleHidden
 * @property self $styleInvert
 * @property self $styleUnderline
 *
 * @method self dim(string $text, string $end = "\n") Print dim text
 * @method self error(string $text, string $end = "\n") Print error text
 * @method self info(string $text, string $end = "\n") Print info text
 * @method self ok(string $text, string $end = "\n") Print success text
 * @method self warn(string $text, string $end = "\n") Print warning text
 */
class Output
{
	/**
	 * Style attributes
	 *
	 * @var array
	 */
	private static $attributes = [
		'styleBold' => 1,
		'styleDim' => 2,
		'styleUnderline' => 4,
		'styleBlink' => 5,
		'styleInvert' => 7,
		'styleHidden' => 8
	];

	/**
	 * Style colors
	 *
	 * @var array
	 */
	private static $colors = [
		'colorBlack' => 30,
		'colorRed' => 31,
		'colorGreen' => 32,
		'colorYellow' => 33,
		'colorBlue' => 34,
		'colorPurple' => 35,
		'colorCyan' => 36,
		'colorGray' => 90,
		'colorWhite' => 97,

		'colorLightGray' => 37,
		'colorLightRed' => 91,
		'colorLightGreen' => 92,
		'colorLightYellow' => 93,
		'colorLigthBlue' => 94,
		'colorLightPurple' => 95,
		'colorLightCyan' => 96
	];

	/**
	 * Has style flag
	 *
	 * @var boolean
	 */
	private bool $hasStyle = false;

	/**
	 * Styles
	 *
	 * @var array
	 */
	private array $style;

	/**
	 * Style callback
	 *
	 * @var array
	 */
	private static array $styleCallbacks = [];

	/**
	 * Init
	 */
	public function __construct()
	{
		$this->reset();

		$defaultStyleCallbacks = [
			'dim' => function (string $text = '', string $end = "\n")
			{
				$this->styleDim;
				$this->stdout($this->stylize($text), $end);
			},
			'error' => function (string $text = '', string $end = "\n")
			{
				$this->bgRed;
				$this->stderr($this->stylize($text), $end);
			},
			'info' => function (string $text = '', string $end = "\n")
			{
				$this->colorBlue;
				$this->stdout($this->stylize($text), $end);
			},
			'ok' => function (string $text = '', string $end = "\n")
			{
				$this->colorGreen;
				$this->stdout($this->stylize($text), $end);
			},
			'warn' => function (string $text = '', string $end = "\n")
			{
				$this->colorYellow;
				$this->stdout($this->stylize($text), $end);
			},
		];

		// register default style callbacks
		foreach ($defaultStyleCallbacks as $name => $fn)
		{
			if (!isset(self::$styleCallbacks[$name]))
			{
				$this->register($name, $fn);
			}
		}
	}

	/**
	 * Invoke style callback
	 *
	 * @param string $name
	 * @param array $args
	 * @return self
	 */
	public function __call(string $name, array $args): self
	{
		if (isset(self::$styleCallbacks[$name]))
		{
			(self::$styleCallbacks[$name])($args[0], $args[1] ?? "\n");
			return $this;
		}

		// handle invalid style callback
		$trace = debug_backtrace();
		trigger_error(sprintf(
			'Call to undefined method %s in %s on line %s',
			self::class . '::' . $name,
			$trace[0]['file'],
			$trace[0]['line']
		));

		return $this;
	}

	/**
	 * Prop style setter
	 *
	 * @param string $name
	 * @return self
	 */
	public function __get(string $name): self
	{
		if ($this->applyStyle($name))
		{
			return $this;
		}

		// handle invalid prop
		$trace = debug_backtrace();
		trigger_error(sprintf(
			'Undefined property: %s in %s on line %s',
			self::class . '::$' . $name,
			$trace[0]['file'],
			$trace[0]['line']
		));

		return $this;
	}

	/**
	 * Apply style
	 *
	 * @param string $name
	 * @return boolean
	 */
	private function applyStyle(string $name): bool
	{
		$isStyle = false;

		if (isset(self::$attributes[$name]))
		{
			$isStyle = true;
			$this->style['attribute'] = self::$attributes[$name];
		}
		else if (substr($name, 0, 2) === 'bg') // bg
		{
			$isStyle = true;
			$this->style['bg'] = self::$colors['color' . substr($name, 2, strlen($name))];
		}
		else if (isset(self::$colors[$name]))
		{
			$isStyle = true;
			$this->style['color'] = self::$colors[$name];
		}

		if ($isStyle)
		{
			$this->hasStyle = true;
		}

		return $isStyle;
	}

	/**
	 * Output text (stdout)
	 *
	 * @param string $text
	 * @param string $end
	 * @return self
	 */
	public function echo(string $text = '', string $end = "\n"): self
	{
		$this->stdout($this->stylize($text), $end);
		return $this;
	}

	/**
	 * Grid helper
	 *
	 * @param array $data
	 * @param array $options
	 * @return self
	 */
	public function grid(array $data, array $options = []): self
	{
		$options += ['indent' => 0, 'padding' => 4, 'style' => []];
		$colPad = [];

		foreach ($data as $line)
		{
			foreach ($line as $k => $v)
			{
				if (!isset($colPad[$k]))
				{
					$colPad[$k] = 0;
				}

				if (($len = strlen((string)$v)) > $colPad[$k])
				{
					$colPad[$k] = $len;
				}
			}
		}

		$keyFirst = array_key_first($colPad);
		$keyLast = array_key_last($colPad);
		foreach ($data as $line)
		{
			foreach ($line as $k => $v)
			{
				$indent = $k === $keyFirst && $options['indent']
					? str_repeat(' ', $options['indent']) : '';
				$pad = $k === $keyLast // dont pad last item
					? '' : str_repeat(' ', ($colPad[$k] - strlen((string)$v)) + $options['padding']);

				if (isset($options['style'][$k]))
				{
					if (is_array($options['style'][$k]))
					{
						foreach ($options['style'][$k] as $style)
						{
							$this->applyStyle($style);
						}
					}
					else if (is_string($options['style'][$k]))
					{
						$this->applyStyle($options['style'][$k]);
					}
				}

				$this->echo($indent . $v . $pad, '');
			}
			$this->echo();
		}

		return $this;
	}

	/**
	 * Register style callback
	 *
	 * @param string $name
	 * @param callable $callback
	 * @return void
	 */
	public static function register(string $name, callable $callback): void
	{
		if (method_exists(Output::class, $name))
		{
			throw new CliException('Cannot register output style method, method already exists', [
				'name' => $name
			]);
		}

		self::$styleCallbacks[$name] = $callback;
	}

	/**
	 * Reset styles
	 *
	 * @return void
	 */
	private function reset(): void
	{
		$this->hasStyle = false;
		$this->style = [
			'attribute' => null,
			'bg' => null,
			'color' => null,
			'indent' => ''
		];
	}

	/**
	 * Write to stderr
	 *
	 * @param string $text
	 * @param string $end
	 * @return self
	 */
	public function stderr(string $text, string $end = "\n"): self
	{
		fwrite(STDERR, $text . $end);
		return $this;
	}

	/**
	 * Write to stdout
	 *
	 * @param string $text
	 * @param string $end
	 * @return self
	 */
	public function stdout(string $text = '', string $end = "\n"): self
	{
		fwrite(STDOUT, $text . $end);
		return $this;
	}

	/**
	 * Style indent setter
	 *
	 * @param integer $number
	 * @return self
	 */
	public function styleIndent(int $number): self
	{
		$this->style['indent'] = str_repeat(' ', $number);
		return $this;
	}

	/**
	 * Stylize text
	 *
	 * @param string $text
	 * @return string
	 */
	private function stylize(string $text): string
	{
		$text = $this->style['indent'] . $text;

		if ($this->hasStyle)
		{
			$parts = [];

			if ($this->style['attribute'])
			{
				$parts[] = $this->style['attribute'];
			}

			if ($this->style['color'])
			{
				$parts[] = $this->style['color'];
			}

			if ($this->style['bg'])
			{
				$parts[] = $this->style['bg'] + 10;
				$text = " {$text} "; // auto padding
			}

			$text = "\033[" . implode(';', $parts) . "m{$text}\033[0m";
		}

		$this->reset();
		return $text;
	}
}
