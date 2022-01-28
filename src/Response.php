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

/**
 * Response
 *
 * @author Shay Anderson
 */
class Response extends \Lark\Factory\Singleton
{
	/**
	 * HTTP status codes
	 */
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_MOVED_TEMPORARILY = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	const HTTP_REQUEST_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_GONE = 410;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_PRECONDITION_FAILED = 412;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	const HTTP_REQUEST_URI_TOO_LONG = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;

	/**
	 * Disable client cache in headers
	 *
	 * @return self
	 */
	public function cacheOff(): self
	{
		if (!headers_sent())
		{
			header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache');
		}
		return $this;
	}

	/**
	 * Content-type setter
	 *
	 * @param string $contentType
	 * @return self
	 */
	public function contentType(string $contentType): self
	{
		$this->header('Content-Type', $contentType);
		return $this;
	}

	/**
	 * Cookie setter
	 *
	 * @param string $key
	 * @param string $value
	 * @param string|int $expires (ex: "+30 days", or: time() + 3600 (1 hour))
	 * @param string $path
	 * @param string $domain
	 * @param bool $secure (transmit cookie only over HTTPS connection)
	 * @param bool $httpOnly (accessible only in HTTP protocol)
	 * @return bool
	 */
	public function cookie(
		string $key,
		string $value,
		$expires = '+1 day',
		string $path = '/',
		string $domain = '',
		bool $secure = false,
		bool $httpOnly = false
	): bool
	{
		if (headers_sent())
		{
			return false;
		}

		return setcookie(
			$key,
			$value,
			is_string($expires) ? strtotime($expires) : $expires,
			$path,
			$domain,
			$secure,
			$httpOnly
		);
	}

	/**
	 * Delete cookie
	 *
	 * @param string $key
	 * @param string $path
	 * @return bool
	 */
	public function cookieClear(string $key, string $path = '/'): bool
	{
		unset($_COOKIE[$key]);
		return $this->cookie($key, '', time() - 3600, $path);
	}

	/**
	 * Header setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return self
	 */
	public function header(string $key, $value): self
	{
		if (!headers_sent())
		{
			header($key . ': ' . $value);
		}
		return $this;
	}

	/**
	 * Remove header
	 *
	 * @param string $key
	 * @return self
	 */
	public function headerClear(string $key): self
	{
		header_remove($key);
		return $this;
	}

	/**
	 * Headers setter
	 *
	 * @param array $headers
	 * @return self
	 */
	public function headers(array $headers): self
	{
		foreach ($headers as $k => $v)
		{
			$this->header($k, $v);
		}
		return $this;
	}

	/**
	 * Send JSON payload with app/JSON content-type header
	 *
	 * @param array|object $data
	 * @return void
	 */
	public function json($data): void
	{
		$this->contentType('application/json');

		if (App::getInstance()->request()->query('pretty')->has()) // pretty
		{
			echo json_encode($data, JSON_PRETTY_PRINT) . PHP_EOL;
		}
		else
		{
			echo json_encode($data);
		}

		exit; #todo replace with App::stop()
	}

	/**
	 * Send redirect (exists application)
	 *
	 * @param string $location
	 * @param bool $statusCode301 (send 301 status code)
	 * @return void
	 */
	public function redirect(string $location, bool $statusCode301 = false): void
	{
		if (!headers_sent())
		{
			if ($statusCode301)
			{
				header('Location: ' . $location, true, self::HTTP_MOVED_PERMANENTLY);
			}
			else
			{
				header('Location: ' . $location);
			}
			exit; #todo replace with App::stop()
		}
	}

	/**
	 * Send raw data payload
	 *
	 * @param [type] $data
	 * @return void
	 */
	public function send($data): void
	{
		echo $data;
		exit; #todo replace with App::stop()
	}

	/**
	 * Status code setter
	 *
	 * @param int $code
	 * @return self
	 */
	public function status(int $code): self
	{
		if (!headers_sent())
		{
			http_response_code($code);
		}
		return $this;
	}
}
