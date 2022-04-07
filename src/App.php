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

use Lark\Filter;
use Lark\Store\DB;
use Lark\Store\StoreException;

/**
 * Lark app
 *
 * @author Shay Anderson
 */
class App extends \Lark\Factory\Singleton
{
	/**
	 * Binding helper
	 *
	 * @param string $path
	 * @param mixed $args
	 * @return void
	 */
	final public function bind(string $path, $args): void
	{
		Binding::set($path, $args);
	}

	/**
	 * Debug #todo mv + improve
	 * @param string|null $message
	 * @param mixed ...$context
	 * @return void
	 */
	final public static function debug(?string $message, ...$context): void
	{
		if (defined('\LARK_DEBUG') && \LARK_DEBUG)
		{
			(new Logger('lark'))->debug($message, ...$context);
		}
	}

	/**
	 * Filter helper
	 *
	 * @return \Lark\Filter
	 */
	final public function filter(): Filter
	{
		return Filter::getInstance();
	}

	/**
	 * Check if interface type is CLI
	 *
	 * @return boolean
	 */
	final public static function isCli(): bool
	{
		return PHP_SAPI === 'cli';
	}

	/**
	 * Request helper
	 *
	 * @return \Lark\Request
	 */
	final public function request(): Request
	{
		return Request::getInstance();
	}

	/**
	 * Run app
	 *
	 * @return void
	 */
	final public function run(): void
	{
		Router::getInstance()->dispatch();
	}

	/**
	 * Response helper
	 *
	 * @return \Lark\Response
	 */
	final public function response(): Response
	{
		return Response::getInstance();
	}

	/**
	 * Session helper
	 *
	 * @return \Lark\Request\Session
	 */
	final public function session(): Request\Session
	{
		return Request\Session::getInstance();
	}

	/**
	 * Share getter/setter helper
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return mixed
	 */
	final public function share(string $key, $value = null)
	{
		if (func_num_args() === 1) // getter
		{
			return Share::getInstance()->get($key);
		}

		// setter
		Share::getInstance()->set($key, $value);
	}

	/**
	 * Store factory
	 *
	 * @param string $name
	 * @return \Lark\StoreInterface
	 */
	final public function store(string $name): \Lark\StoreInterface
	{
		$name = explode('.', $name);
		$type = $name[0] ?? null;

		switch ($type ?? null)
		{
			case 'db':
				if (count($name) === 3) // default connection
				{
					return DB::getInstance()->client($name[1], $name[2]);
				}
				else
				{
					return DB::getInstance()->client($name[2], $name[3], $name[1]);
				}
				break;

			default:
				throw new StoreException('Invalid store type "' . $type . '"', [
					'name' => $name
				]);
				break;
		}
	}

	/**
	 * Validator helper
	 *
	 * @param array|object $data
	 * @param array $schema
	 * @param int $flags
	 * @return Validator
	 */
	final public function validator($data, array $schema, int $entityFlags = 0): Validator
	{
		return new Validator($data, $schema, $entityFlags);
	}
}
