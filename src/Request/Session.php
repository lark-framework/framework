<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Request;

use Lark\App;
use Lark\Map\Path as MapPath;

/**
 * Request session
 *
 * @author Shay Anderson
 */
class Session extends \Lark\Factory\Singleton
{
	/**
	 * Cookie options
	 *
	 * @var array
	 */
	private static array $cookie;

	/**
	 * Session state
	 *
	 * @var bool
	 */
	private bool $state = false;

	/**
	 * Init
	 */
	protected function __construct()
	{
		$this->start();
	}

	/**
	 * Clear
	 *
	 * @param string $key
	 * @return void
	 */
	public function clear(string $key): void
	{
		MapPath::clear($_SESSION, $key);
	}

	/**
	 * Session cookie options setter
	 *
	 * @param array $options
	 * @return void
	 */
	public static function cookieOptions(array $options): void
	{
		self::$cookie = $options;
	}

	/**
	 * Destroy session
	 *
	 * @return void
	 */
	public function destroy(): void
	{
		if ($this->state)
		{
			$_SESSION = [];

			if (ini_get('session.use_cookies')) // delete session cookie
			{
				$a = session_get_cookie_params();
				setcookie(
					session_name(),
					'',
					time() - 42000,
					$a['path'],
					$a['domain'],
					$a['secure'],
					$a['httponly']
				);
			}

			session_regenerate_id();
			$this->state = !session_destroy();
		}

		App::debug(__METHOD__, [
			'session' => $this->state
		]);
	}

	/**
	 * Flash object getter
	 *
	 * @return \Lark\Request\SessionFlash
	 */
	public function flash(): SessionFlash
	{
		return SessionFlash::getInstance();
	}

	/**
	 * Getter
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get(string $key)
	{
		return MapPath::get($_SESSION, $key);
	}

	/**
	 * Check if key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool
	{
		if (!$_SESSION)
		{
			return false;
		}

		return MapPath::has($_SESSION, $key);
	}

	/**
	 * Check if session exists
	 *
	 * @return bool
	 */
	public function isSession(): bool
	{
		return $this->state;
	}

	/**
	 * Setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function set(string $key, $value): void
	{
		MapPath::set($_SESSION, $key, $value);
	}

	/**
	 * Start session
	 *
	 * @return void
	 */
	private function start(): void
	{
		if (!$this->state)
		{
			if (self::$cookie) // cookie options
			{
				$options = self::$cookie + [
					'lifetime' => 0,
					'path' => '/',
					'domain' => '',
					'secure' => false,
					'httponly' => false
				];

				session_set_cookie_params(
					$options['lifetime'],
					$options['path'],
					$options['domain'],
					$options['secure'],
					$options['httponly']
				);
			}

			session_cache_limiter('');
			$this->state = session_start();

			App::debug(__METHOD__, [
				'session' => $this->state
			]);
		}
	}

	/**
	 * Session array getter
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return $_SESSION ?: [];
	}
}
