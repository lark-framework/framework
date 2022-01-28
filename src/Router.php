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
use Lark\Router\Route;
use Lark\Router\NotFoundException;
use Lark\Router\RouterException;

/**
 * Router
 *
 * @author Shay Anderson
 */
class Router extends \Lark\Factory\Singleton
{
	/**
	 * HTTP request methods
	 *
	 * @var array
	 */
	private static array $methods = ['DELETE', 'GET', 'HEAD', 'OPTIONS', 'PATCH', 'POST', 'PUT'];

	/**
	 * Middleware for group
	 *
	 * @var array
	 */
	private static array $middlewareGroup = [];

	/**
	 * Registered middleware
	 *
	 * @var array
	 */
	private static array $middlewarePending = [];

	/**
	 * Not found action
	 *
	 * @var Closure|null
	 */
	private ?Closure $notFoundAction = null;

	/**
	 * Request method
	 *
	 * @var string
	 */
	private string $requestMethod;

	/**
	 * Reqest path
	 *
	 * @var string
	 */
	private string $requestPath;

	/**
	 * Matched route
	 *
	 * @var Route|null
	 */
	private ?Route $route = null;

	/**
	 * Route base
	 *
	 * @var string
	 */
	private static string $routeBase = '';

	/**
	 * Loader route base
	 *
	 * @var string
	 */
	private static string $routeBaseLoad = '';

	/**
	 * Init
	 */
	protected function __construct()
	{
		$this->requestMethod = Request::getInstance()->method();
		$this->requestPath = Request::getInstance()->path();

		App::debug(__METHOD__, [
			'method' => $this->requestMethod,
			'path' => $this->requestPath
		]);
	}

	/**
	 * Route setter for all HTTP methods
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function all(string $route, $action): self
	{
		return $this->routeAdd(self::$methods, $route, $action);
	}

	/**
	 * Router setter for HTTP DELETE method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function delete(string $route, $action): self
	{
		return $this->routeAdd('DELETE', $route, $action);
	}

	/**
	 * Middleware binding
	 *
	 * @param Closure|array ...$middleware
	 * @return self
	 */
	public function bind(...$middleware): self
	{
		foreach ($middleware as $action)
		{
			// fn OR [class, method]
			if (
				$action instanceof Closure
				|| (is_array($action) && isset($action[0]) && is_string($action[0]))
			)
			{
				Route::action($action, true);
				continue;
			}

			// array of actions
			foreach ($action as $v)
			{
				$this->bind($v);
			}
		}

		return $this;
	}

	/**
	 * Dispatcher
	 *
	 * @return void
	 */
	public function dispatch(): void
	{
		App::debug(__METHOD__);

		// import routes
		require_once PATH_APP . '/routes.php';

		// no match
		if (!$this->route)
		{
			#todo set response code as 404

			if ($this->notFoundAction)
			{
				($this->notFoundAction)($this->requestMethod, $this->requestPath);
			}
			else
			{
				throw new NotFoundException('Route not found', [
					'requestMethod' => $this->requestMethod,
					'requestPath' => $this->requestPath
				]);
			}
		}
	}

	/**
	 * Route setter for HTTP GET method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function get(string $route, $action): self
	{
		return $this->routeAdd('GET', $route, $action);
	}

	/**
	 * Instance getter (and reset scoped vars)
	 *
	 * @return \Lark\Factory\Singleton
	 */
	public static function getInstance(): \Lark\Factory\Singleton
	{
		// reset
		self::$routeBase = '';
		self::$middlewareGroup = [];
		if (self::$routeBaseLoad) // route base from load()
		{
			self::$routeBase = self::$routeBaseLoad;
			self::$routeBaseLoad = '';
		}

		return parent::getInstance();
	}

	/**
	 * Group setter
	 *
	 * @param string $baseRoute
	 * @param Closure|array ...$middleware
	 * @return self
	 */
	public function group(string $baseRoute, ...$middleware): self
	{
		if ($baseRoute === '/')
		{
			throw new RouterException('Route groups cannot have base route of "/"');
		}

		self::$routeBase = $baseRoute;
		self::$middlewareGroup = $middleware;
		return $this;
	}

	/**
	 * Route setter for HTTP HEAD method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function head(string $route, $action): self
	{
		return $this->routeAdd('HEAD', $route, $action);
	}

	/**
	 * Load group(s) from file(s)
	 *
	 * @param array $groupToFile ([group => file, ...])
	 * @return void
	 */
	public function load(array $groupToFile): void
	{
		if ($this->requestPath === '/')
		{
			return; // no group
		}

		foreach ($groupToFile as $group => $file)
		{
			if ($group === '/' || $group === '')
			{
				throw new RouterException('Loading route groups cannot have base route of "/"');
			}

			if ($group === substr($this->requestPath, 0, strlen($group)))
			{
				$filePath = PATH_APP_ROUTES . '/' . $file . '.php';

				$context = [
					'group' => $group,
					'file' => $file,
					'filePath' => $filePath
				];

				App::debug(__METHOD__, $context);

				if (!is_file($filePath))
				{
					throw new RouterException(
						'Group "' . $group . '" file "' . $file . '" does not exist',
						$context
					);
				}

				self::$routeBaseLoad = $group;
				require_once $filePath;
				self::$routeBaseLoad = ''; // reset, scoped call my not be in group file
				return;
			}
		}
	}

	/**
	 * Route middleware binding
	 *
	 * @param array $methods
	 * @param string $route
	 * @param Closure|array ...$middleware
	 * @return self
	 */
	public function map(array $methods, string $route, ...$middleware): self
	{
		return $this->routeAdd($methods, $route, $middleware, true);
	}

	/**
	 * Middleware binding for any matched routes
	 *
	 * @param Closure|array ...$middleware
	 * @return self
	 */
	public function matched(...$middleware): self
	{
		self::$middlewarePending[] = $middleware;
		return $this;
	}

	/**
	 * Not found action setter
	 *
	 * @param Closure $action
	 * @return void
	 */
	public function notFound(Closure $action): void
	{
		$this->notFoundAction = $action;
	}

	/**
	 * Route setter for HTTP OPTOINS method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function options(string $route, $action): self
	{
		return $this->routeAdd('OPTIONS', $route, $action);
	}

	/**
	 * Route setter for HTTP PATCH method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function patch(string $route, $action): self
	{
		return $this->routeAdd('PATCH', $route, $action);
	}

	/**
	 * Route setter for HTTP POST method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function post(string $route, $action): self
	{
		return $this->routeAdd('POST', $route, $action);
	}

	/**
	 * Route setter for HTTP PUT method
	 *
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function put(string $route, $action): self
	{
		return $this->routeAdd('PUT', $route, $action);
	}

	/**
	 * Route setter for multiple HTTP methods
	 *
	 * @param array $methods
	 * @param string $route
	 * @param Closure|array $action
	 * @return self
	 */
	public function route(array $methods, string $route, $action): self
	{
		return $this->routeAdd($methods, $route, $action);
	}

	/**
	 * Route setter
	 *
	 * @param array|string $method
	 * @param string $route
	 * @param Closure|array $action
	 * @param boolean $isMiddleware
	 * @return self
	 */
	private function routeAdd($method, string $route, $action, bool $isMiddleware = false): self
	{
		if ($this->route) // already matched
		{
			return $this;
		}

		if (!is_array($method))
		{
			$method = [$method];
		}

		// validate method(s)
		foreach ($method as $v)
		{
			if (!in_array($v, self::$methods))
			{
				throw new RouterException('Invalid HTTP method', [
					'method' => $v,
					'route' => $route
				]);
			}
		}

		// verify method for request
		if (!in_array($this->requestMethod, $method))
		{
			return $this;
		}

		$route = new Route($route, !$isMiddleware ? self::$routeBase : '');

		if ($route->match($this->requestPath))
		{
			App::debug(__METHOD__, [
				'match' => true,
				'route' => $route
			]);

			if (!$isMiddleware)
			{
				// execute pending middleware (on match)
				if (count(self::$middlewarePending))
				{
					$this->bind(self::$middlewarePending);
				}

				// execute group middleware
				if (count(self::$middlewareGroup))
				{
					$this->bind(self::$middlewareGroup);
				}

				// execute action
				Route::action($action, $isMiddleware, $route->getParams());

				// set as route
				$this->route = &$route;
			}
			else // route specific middleware
			{
				$this->bind($action);
			}
		}

		return $this;
	}
}
