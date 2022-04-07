<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Router;

use Closure;
use Lark\App;

/**
 * Router route
 *
 * @author Shay Anderson
 */
class Route
{
	/**
	 * Params
	 *
	 * @var array
	 */
	private array $params = [];

	/**
	 * Route
	 *
	 * @var string
	 */
	private string $route;

	/**
	 * Init
	 *
	 * @param string $route
	 * @param string $routeBase
	 */
	public function __construct(string $route, string $routeBase)
	{
		$this->route = ltrim($route, '/');
		if ($this->route)
		{
			$this->route = $routeBase . '/' . $this->route;
		}
		else if ($routeBase)
		{
			$this->route = $routeBase;
		}
		else
		{
			$this->route = $route;
		}

		App::debug(__METHOD__, [
			'route' => $this->route,
			'routeBase' => $routeBase
		]);
	}

	/**
	 * Invoke route action
	 *
	 * @param Closure|array $action
	 * @param boolean $isMiddleware
	 * @param array $params
	 * @return void
	 */
	public static function action($action, bool $isMiddleware, array $params = [])
	{
		// fn
		if ($action instanceof Closure)
		{
			App::debug(__METHOD__, [
				'action' => '[fn]',
				'isMiddleware' => $isMiddleware,
				'params' => $params
			]);

			call_user_func_array($action, $params);
		}
		// [class, method, params?]
		else if (is_array($action))
		{
			if (!isset($action[0]) || !isset($action[1]))
			{
				throw new RouterException('Invalid Route action for class method', $action);
			}

			App::debug(__METHOD__, [
				'action' => $action,
				'isMiddleware' => $isMiddleware,
				'params' => $params
			]);

			call_user_func_array([new $action[0], $action[1]], $params);
		}
		else
		{
			$type = gettype($action);
			throw new RouterException('Invalid Route action type', [
				'type' => $type,
				'isMiddleware' => $isMiddleware,
				'params' => $params
			]);
		}
	}

	/**
	 * Params getter
	 *
	 * @return array
	 */
	public function &getParams(): array
	{
		return $this->params;
	}

	/**
	 * Check for route match
	 *
	 * @param string $requestPath
	 * @return boolean
	 */
	public function match(string $requestPath): bool
	{
		$isOptionalParam = strpos($this->route, '?') !== false;
		$p = $this->route;

		if ($isOptionalParam)
		{
			// replace optional params: "/{param?}" with "/?([^/]+)?"
			$p = preg_replace('/\/{([^}\?]+\?)}/', '/?([^/]+)?', $p);

			// optional params only allowed at end of route
			if (preg_match('/\?\/[^\?]/', $p))
			{
				throw new RouterException('Optional parameters can only exist at the end of a route', [
					'route' => $this->route,
					'pattern' => $p
				]);
			}
		}

		// replace route params: "/{param}" with "/([^/]+)"
		$p = preg_replace('/\/{(.*?)}/', '/([^\/]+)', $p);

		$params = [];
		if ((bool)preg_match_all('#^' . $p . '$#', $requestPath, $m))
		{
			$m = array_slice($m, 1);
			foreach ($m as $a)
			{
				// do not add empty params for optional params
				if ($isOptionalParam && empty($a[0]))
				{
					continue;
				}

				$params[] = $a[0];
			}

			App::debug(__METHOD__, [
				'route' => $this->route,
				'pattern' => $p,
				'params' => $params
			]);

			$this->params = $params;

			return true;
		}

		return false;
	}
}
