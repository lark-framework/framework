<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Http;

/**
 * HTTP client helper
 *
 * @author Shay Anderson
 */
class Client
{
	/**
	 * CURL options
	 *
	 * @var array
	 */
	const CURL = 'curl';

	/**
	 * HTTP headers
	 *
	 * @var array
	 */
	const HEADERS = 'headers';

	/**
	 * Custom port
	 *
	 * @var int
	 */
	const PORT = 'port';

	/**
	 * HTTP proxy
	 *
	 * @var string
	 */
	const PROXY = 'proxy';

	/**
	 * Allow redirects
	 *
	 * @var bool
	 */
	const REDIRECTS = 'redirects';

	/**
	 * Timeout in seconds
	 *
	 * @var int
	 */
	const TIMEOUT = 'timeout';

	/**
	 * Base URL
	 *
	 * @var string
	 */
	const URL = 'url';

	/**
	 * Verify peer's certificate and common name
	 *
	 * @var bool
	 */
	const VERIFY = 'verify';

	/**
	 * Response status code
	 *
	 * @var integer
	 */
	private int $code = 0;

	/**
	 * Response headers
	 *
	 * @var array
	 */
	private array $headers = [];

	/**
	 * Default options
	 *
	 * @var array
	 */
	private array $options = [
		self::REDIRECTS => true,
		self::TIMEOUT => 10,
		self::VERIFY => true
	];

	/**
	 * Init
	 *
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		if (!extension_loaded('curl'))
		{
			throw new Exception('PHP "curl" extension is required when using the Http class');
		}

		$this->options = $options + $this->options;
	}

	/**
	 * DELETE request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function delete(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($url, $params, $options, [
			CURLOPT_CUSTOMREQUEST => 'DELETE'
		]);
	}

	/**
	 * Request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @param array|null $methodOptions
	 * @return mixed
	 */
	private function fetch(string $url, array $params, array $options, array $methodOptions = null)
	{
		// merge options
		$options += $this->options;

		// validate options
		(new \Lark\Validator($options, [
			self::CURL => 'array',
			self::HEADERS => 'array',
			self::PORT => 'int',
			self::PROXY => 'string',
			self::REDIRECTS => 'bool',
			self::TIMEOUT => ['int', ['min' => 1]],
			self::URL => ['string', 'url'],
			self::VERIFY => 'bool'
		]))->assert(function (string $field, string $message)
		{
			throw new HttpClientException("Options field \"{$field}\": {$message}");
		});

		// base URL
		if (isset($options[self::URL]))
		{
			$url = rtrim($options[self::URL], '/') . '/' . ltrim($url, '/');
		}

		// validate URL
		$this->validateUrl($url);

		// init
		$curl = curl_init($url);
		curl_setopt_array($curl, [
			CURLOPT_TIMEOUT => (int)$options[self::TIMEOUT],
			CURLOPT_CONNECTTIMEOUT => (int)$options[self::TIMEOUT],
			CURLOPT_RETURNTRANSFER => true
		]);

		// set method options
		if ($methodOptions)
		{
			curl_setopt_array($curl, $methodOptions);
		}

		// set headers
		$headers = [];
		curl_setopt($curl, CURLOPT_HEADERFUNCTION, function ($curl, $header) use (&$headers)
		{
			$size = strlen($header);
			$parts = explode(':', $header);
			if (count($parts) > 1)
			{
				$headers[strtolower(trim(array_shift($parts)))] = trim(implode(':', $parts));
			}

			return $size;
		});

		// allow redirects
		if (isset($options[self::REDIRECTS]) && $options[self::REDIRECTS] === true)
		{
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		}

		// set postfields
		if ($params)
		{
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		}

		// custom port
		if (isset($options[self::PORT]) && (int)$options[self::PORT])
		{
			curl_setopt($curl, CURLOPT_PORT, (int)$options[self::PORT]);
		}

		// verify peer and name
		if (isset($options[self::VERIFY]) && $options[self::VERIFY] === false)
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		}
		else
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		}

		// proxy
		if (isset($options[self::PROXY]))
		{
			curl_setopt($curl, CURLOPT_PROXY, $options[self::PROXY]);
		}

		// headers
		if (isset($options[self::HEADERS]))
		{
			// convert [key => value] => [0 => key:value]
			if (strpos((string)reset($options[self::HEADERS]), ':') === false)
			{
				$tmp = [];
				foreach ($options[self::HEADERS] as $k => $v)
				{
					$tmp[] = "$k: $v";
				}
				$options[self::HEADERS] = &$tmp;
			}
			curl_setopt($curl, CURLOPT_HTTPHEADER, $options[self::HEADERS]);
		}

		// user curl options
		if (isset($options[self::CURL]))
		{
			curl_setopt_array($curl, $options[self::CURL]);
		}

		$res = curl_exec($curl);
		$this->code = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if ($res === false)
		{
			$error = curl_error($curl);
			throw new HttpClientException($error ?: 'Unknown error', [
				'errno' => (int)curl_errno($curl)
			]);
		}

		curl_close($curl);

		$this->headers = &$headers;

		return $res;
	}

	/**
	 * HEAD request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function head(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($this->urlQuery($url, $params), [], $options, [
			CURLOPT_NOBODY => true
		]);
	}

	/**
	 * GET request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function get(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($this->urlQuery($url, $params), [], $options);
	}

	/**
	 * Response headers getter
	 *
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * Response status code getter
	 *
	 * @return integer
	 */
	public function getStatusCode(): int
	{
		return $this->code;
	}

	/**
	 * OPTIONS request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function options(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($this->urlQuery($url, $params), [], $options, [
			CURLOPT_CUSTOMREQUEST => 'OPTIONS',
			CURLOPT_NOBODY => true
		]);
	}

	/**
	 * PATCH request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function patch(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($url, $params, $options, [
			CURLOPT_CUSTOMREQUEST => 'PATCH'
		]);
	}

	/**
	 * POST request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function post(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($url, $params, $options, [
			CURLOPT_POST => true
		]);
	}

	/**
	 * PUT request
	 *
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return mixed
	 */
	public function put(string $url, array $params = [], array $options = [])
	{
		return $this->fetch($url, $params, $options, [
			CURLOPT_CUSTOMREQUEST => 'PUT'
		]);
	}

	/**
	 * Append query to URL
	 *
	 * @param string $url
	 * @param array $params
	 * @return string
	 */
	public function urlQuery(string $url, array $params): string
	{
		if ($params)
		{
			if (strpos($url, '?') === false) // add query
			{
				$url .= '?' . http_build_query($params);
			}
			else // append query
			{
				$url .= '&' . http_build_query($params);
			}
		}

		return $url;
	}

	/**
	 * URL validator
	 *
	 * @param string $url
	 * @return void
	 */
	private function validateUrl(string $url): void
	{
		if (filter_var($url, FILTER_VALIDATE_URL) === false)
		{
			throw new HttpClientException('Invalid URL', [
				'url' => $url
			]);
		}
	}
}
