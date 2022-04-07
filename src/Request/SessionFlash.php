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

/**
 * Request session flash
 *
 * @author Shay Anderson
 */
class SessionFlash extends \Lark\Factory\Singleton
{
	/**
	 * Session key
	 */
	const SESSION_KEY = '__LARK__';

	/**
	 * Data
	 *
	 * @var array
	 */
	private static $data;

	/**
	 * Session object
	 *
	 * @var \Lark\Request\Session
	 */
	private Session $session;

	/**
	 * Init
	 */
	protected function __init()
	{
		$this->session = Session::getInstance();

		// cache
		self::$data = $this->session->get(self::SESSION_KEY);
		if (self::$data === null)
		{
			self::$data = []; // init
		}

		$this->session->clear(self::SESSION_KEY);
	}

	/**
	 * Getter
	 *
	 * @param string $key
	 * @return mixed (null if not found)
	 */
	public function get(string $key)
	{
		return self::$data[$key] ?? null;
	}

	/**
	 * Check if key exists
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has(string $key): bool
	{
		return isset(self::$data[$key]) || array_key_exists($key, self::$data);
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
		$this->session->set(self::SESSION_KEY . '.' . $key, $value);
		self::$data[$key] = $value;
	}

	/**
	 * Array getter
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		return self::$data;
	}
}
