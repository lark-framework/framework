<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

use App\App;
use Lark\Filter;
use Lark\Logger;
use Lark\Router;

/**
 * App instance getter
 *
 * @return \App\App
 */
function app(): App
{
	return App::getInstance();
}

/**
 * Debug helper
 *
 * @param mixed $message
 * @param mixed $context
 * @return void
 */
function debug($message = null, $context = null): void
{
	(new Logger)->debug($message, $context, 2);
}

/**
 * Environment variables helper
 *
 * @param string $key
 * @param mixed $default
 * @param bool $invalidKeyException (throw exception on invalid key)
 * @return mixed
 * @throws \Lark\Exception (on invalid key)
 */
function env(string $key, $default = null, bool $invalidKeyException = false)
{
	return \Lark\Env::getInstance()->get($key, $default, $invalidKeyException);
}

/**
 * Filter helper
 *
 * @return \Lark\Filter
 */
function filter(): Filter
{
	return \Lark\Filter::getInstance();
}

/**
 * Logger helper
 *
 * @param string $channel
 * @return \Lark\Logger
 */
function logger(string $channel = ''): Logger
{
	return new Logger($channel);
}

/**
 * Var printer
 *
 * @param mixed ...$values
 * @return void
 */
function pa(...$values): void
{
	if (!count($values))
	{
		$values = [null];
	}

	foreach ($values as $v)
	{
		echo is_scalar($v) || $v === null
			? $v . (PHP_SAPI === 'cli' ? PHP_EOL : '<br />')
			: (PHP_SAPI === 'cli' ? print_r($v, true) : '<pre>' . print_r($v, true) . '</pre>');
	}
}

/**
 * Router helper
 *
 * @return \Lark\Router
 */
function router(): Router
{
	return Router::getInstance();
}

/**
 * Store factory helper
 *
 * @param string $name
 * @return \Lark\StoreInterface
 */
function store(string $name): \Lark\StoreInterface
{
	return app()->store($name);
}
