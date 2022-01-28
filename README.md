# Lark Framework

Lark is an app framework for PHP (7.4, 8)

- [Routing](#routing)
	- [Routes](#routes)
	- [Route Parameters](#route-parameters)
	- [Route Actions](#route-actions)
	- [Middleware](#middleware)
- [Logging](#logging)
- [Exception Handling](#exception-handling)
- [Configuration](#configuration)
- [Environment Configuration](#environment-configuration)
- [Request](#request)
- [Response](#response)
- [Share](#share)
- [Model](#model)
- [Store](#store)
- [Validator](#validator)
	- [Validation Types & Rules](#validation-types--rules)
- [Filter](#filter)

## Routing

The router is used to dispatch route actions and middleware.

```php
// bootstrap
// ...

// define routes
router()
	// get([route], [action])
	->get('/', function() {});

// run app
app()->run();
```

### Routes

There are multiple ways to define routes.

```php
// routes for HTTP specific methods:
router()->delete('/route', function(){});
router()->get('/route', function(){});
router()->head('/route', function(){});
router()->options('/route', function(){});
router()->patch('/route', function(){});
router()->post('/route', function(){});
router()->put('/route', function(){});

// route for ALL HTTP methods
router()->all('/route', function(){});
// route for multiple HTTP methods
router()->route(['GET', 'POST'], '/route', function(){});
```

#### Regular Expression Routes

Regular expression routes use [PCRE](https://www.php.net/manual/en/book.pcre.php) patterns for matching routes.

```php
// match all routes that begin with "/api"
router()->get('/api.*?', function(){});
```

#### Route Groups

Route groups can be used to simplify defining similar routes.

```php
router()
	->group('/api/users') // group([base-route])
	->get('/', function(){}) // "/api/users"
	->get('/active', function(){}); // "/api/users/active"
```

#### Route Group Loading

Route groups can be defined in files which are loaded during routing (lazy load routes).

```php
// bootstrap routes directory
// ...

router()->load([
	// [base-route] => [file]
	'/api/users' => 'users'
]);

// in routes directory file "users.php" defines routes
// the group('/api/users') method does not need to be called (handled by load() method)
router()
	->get('/', function(){}) // "/api/users"
	->get('/active', function(){}); // "/api/users/active"
```

### Route Actions

Route actions are executed when a route is matched. Route actions can be a callable function (`Closure`) or array with `[class, method]`. The first route matched is the only route action that will be executed.

```php
// function will be called on route match
router()->get('/example1', function(){});

// class method "\App\Controller\ExampleController::hello()" will be called on route match
router()->get('/example2', [\App\Controller\ExampleController::class, 'hello']);
```

### Route Not Found Action

If no route match is found a not found action can be defined. The HTTP response status code is auto set to `404`.

```php
router()->notFound(function(string $requestMethod, string $requestPath){});
```

If a not found action is not defined a `Lark\Router\NotFoundException` will be thrown.

### Route Parameters

#### Named Parameters

Route named parameters are required parameters that do not use regular expressions. Multiple name parameters are allowed.

```php
router()->get('/users/{id}', function($id){});
```

#### Optional Named Parameters

Route optional named parameters are optional parameters that do not use regular expressions. Optional named parameters can only be used at the end of the route. Multiple optional named parameters are allowed.

```php
router()->get('/users/{id}/{groupId?}', function($id, $groupId = null){});
```

In this example the `groupId` parameter is optional, so route `/users/5` and `/users/5/10` would both match.

#### Regular Expression Parameters

Regular expressions can be used to define parameters using [PCRE](https://www.php.net/manual/en/book.pcre.php) patterns. Multiple regular expression parameters are allowed.

```php
// match digits
router()->get('/users/(\d+)', function($id){});
```

### Middleware

Middleware is a single or multiple actions that are executed before a route action is called. Middleware actions can be executed always or only when a route is matched. Middleware must be defined _before_ routes are defined. Middleware actions follow the same structure as [Route Actions](#route-actions).

```php
// executed always
router()->bind(function(){});
// executed if any route is matched
router()->matched(function(){});

// define routes
// ...
```

Multiple middleware actions can be set.

```php
// single action
router()->bind(function(){});
// multiple actions
router()->bind(function(){}, [MyController::class, 'myMethod']);
// array of actions
router()->bind([
	function(){},
	function(){}
]);
```

#### Route Middleware

Route specific middleware actions are only executed if the route is matched.

```php
// method([methods], [route], [...actions])
router()->map(['GET'], '/api.*?', function(){});

router()->get('/api/users', function(){});
```

If the HTTP request is `/api/users` then both the middleware action and route action would be executed.

#### Route Group Middleware

Middleware can be defined to be used only on a specific route group. Route group middleware actions are only executed if a group route is matched.

```php
router()
	// group([base-route], [...actions])
	->group('/api/users', function(){})
	->get('/', function(){}) // "/api/users"
	->get('/{id}', function($id){}) // "/api/users/{id}"
```

## Logging

`Lark\Logger` is a logging helper.

```php
logger('channel')->critical('message', [context]);
logger('channel')->debug('message', [context]);
logger('channel')->error('message', [context]);
logger('channel')->info('message', [context]);
logger('channel')->warning('message', [context]);
```

Logging info level record example.

```php
// bootstrap log handler
app()->share('logHandler', new \App\LogHandler);
\Lark\Logger::handler(app()->share('logHandler'));

// ...

// log info level record
logger('user')->info('User has been authorized', ['userId' => $user->id]);

// ...

// output log example
print_r( app()->share('logHandler')->close() );
```

The `debug()` helper is available for debug level log records.

```php
debug(__METHOD__, ['context']);
debug(['more' => 'info']);
```

Global context can be added to all context sent in log record.

```php
\Lark\Logger::globalContext(['sessionId' => $session->id]);
// ...
logger('user')->info('User has signed out', ['userId' => $user->id]);
// context is: ['sessionId' => x, 'userId' => y]
```

## Exception Handling

Exceptions can be handled using the exception handler.

```php
// bootstrap
// ...

// define routes
// ...

try
{
	// run app
	app()->run();
}
catch (Throwable $th)
{
	new \App\ExceptionHandler($th);
}
```

Example `App\ExceptionHandler` class.

```php
namespace App;
use Throwable;
class ExceptionHandler
{
	public function __construct(Throwable $th)
	{
		\Lark\Exception::handle($th, function (array $info) use (&$th)
		{
			$code = $th->getCode();
			if (!$code)
			{
				$code = 500;
			}

			// log error
			// ...

			// respond with error
			app()->response()
				->status($code)
				->json($info);

			// --or-- continue to throw exception
			throw $th;
		});
	}
}
```

## Configuration
Framework configuration settings and bindings can be set using the `bind()` method.

### Store Connections
Store connections are registered using the sytnax `store.[type].[connection].[database].[collection]`.
```php
// the first connection for each store types is always the default
app()->bind('store.db.connection.myConn', [ /* hosts, username, etc. */ ]);
// use like (default no connection name required):
$db = store('db.dbName.collectionName');

// not default connection:
app()->bind('store.db.connection.myConn2', [ /* hosts, username, etc. */ ]);
// use like:
$db2 = store('db.myConn2.dbName.collectionName');
```

### Store Global Options
Store global options can be set using `store.[type].options`.
```php
app()->bind('db.db.options', [
	// enable debug logging (default: false)
	'debug.log' => true
	// limit auto applied to each query (default: 10000)
	'find.limit' => 5000
]);
```

### Validator Custom Rules
Custom validator rules can be registered using `validator.rule.[type].[ruleClassName]`.
```php
app()->bind('validator.rule.string.beginWithEndWith', \App\Validator\BeginWithEndWith::class);
```

## Environment Configuration

`Lark\Env` is an app environment configuration helper. The helper function `env()` is available by default.

Example `.env` file.

```
DB_USER=myuser
DB_PWD=secret
```

Example usage.

```php
// load from file (bootstrap)
\Lark\Env::getInstance()->load(PATH_APP . '/.env');

$dbUser = env('DB_USER'); // myuser
$dbPassword = env('DB_PWD'); // secret

// return default value "default" if key does not exist
$dbName = env('DB_NAME', 'default');

// for required env keys
$dbHost = env('DB_HOST', null, /* throw exception on missing key */ true);
// Lark\Exception exception thrown: Invalid key "DB_HOST"
```

Other `Lark\Env` methods: `fromArray(array $array)`, `has(string $key): bool` and `toArray(): array`.

## Request

`Lark\Request` is an HTTP request helper with input sanitizing.

`POST` request ( `Content-Type: application/json` ) with JSON body like `{"name": "Shay", "contact": {"email": "example@example.com"}}` example.

```php
$data = app()->request()->json(); // get all as object (no auto sanitizing)

// or get individual fields
$name = app()->request()->json('name')->string();
if(app()->request()->json('contact.email')->has())
{
	$email = app()->request()->json('contact.email')->email();
}
```

`POST` request ( `Content-Type: application/x-www-form-urlencoded` ) example.

```php
if(app()->request()->isMethod('POST'))
{
	$name = app()->request()->input('name')->string();
	if(app()->request()->input('email')->has())
	{
		$email = app()->request()->input('email')->email();
	}
}
```

`GET` request example.

```php
// request "/?id=5&name=Shay"
print_r([
	'id' => app()->request()->query('id')->integer(),
	// use "default" as value if query "name" does not exist
	'name' => app()->request()->query('name', 'default')->string()
]); // Array ( [id] => 5 [name] => Shay )
```
> Filter options and [flags](https://www.php.net/manual/en/filter.filters.flags.php) can be used with filter methods: `app()->request()->input('name')->string(['flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK])`

Request cookie example.

```php
if(app()->request()->cookie('myCookie')->has())
{
	var_dump( app()->request()->cookie('myCookie')->string() );
}
```

### Request Session

`Lark\Request\Session` is a session helper.

```php
app()->session->set('user.id', 5); // creates session data: [user => [id => 5]]
// ...
if(app()->session->has('user.id'))
{
	$userId = app()->session->get('user.id');
}
```

`Lark\Request\SessionFlash` can be used to store short-term data where the data is available from when set through the following request, example:

```php
app()->session()->flash()->set('userError', 'Invalid session');
// redirect, then use message
echo app()->session()->flash()->get('userError');
// message is no longer available on next request
```

### Request Methods

- `body(bool $convertHtmlEntities = true): string` - request raw body data getter
- `contentType(): string` - content-type getter
- `cookie(string $key, $default = null): Lark\Request\Cookie` - cookie input object getter
- `hasHeader(string $key): bool` - check if header key exists
- `header(string $key): string` - header value getter
- `headers(): array` - get all headers
- `host(): string` - HTTP host value getter, like `www.example.com`
- `input(string $key, $default = null): Lark\Request\Input` - input object getter for `POST`
- `ipAddress(): string` - IP address getter
- `isContentType(string $contentType): bool` - validate request content-type
- `isMethod(string $method): bool` - validate request method
- `isSecure(): bool` - check if request is secure (HTTPS)
- `json($fieldOrReturnArray = false, $default = null)` - JSON request body helper
- `method(): string` - request method getter
- `path(): string` - path getter, like `/the/path`
- `pathWithQueryString(): string` - path with query string getter, like `/the/path?x=1`
- `port(): int` - port getter
- `query(string $key, $default = null): Lark\Request\Query` - query input object getter for `GET`
- `queryString(): string` - query string getter, like `x=1&y=2`
- `scheme(): string` - URI scheme getter, like `http`
- `session(): Lark\Request\Session` - session object getter
- `uri(): string` - URI getter, like `http://example.com/example?key=x`

### Request Input Methods

Input methods include methods for request input objects: `Cookie`, `Input` and `Query`.

- `email(array $options = [])` - value getter, sanitize as email
- `float(array $options = ['flags' => FILTER_FLAG_ALLOW_FRACTION])` - value getter, sanitize as float
- `has(): bool` - check if key exists
- `integer(array $options = [])` - value getter, sanitize as integer
- `string(array $options = [])` - value getter, sanitize as string
- `url(array $options = [])` - value getter, sanitize as URL

### Session Methods

Session methods `clear()`, `get()`, `has()` and `set()` all use dot notation for keys, for example: `set('user.isActive', 1) equals: [user => [isActive => 1]]`.

- `clear(string $key)` - clear a key
- `static cookieOptions(array $options)` - set cookie options
	- default options are: `['lifetime' => 0, 'path' => '/', 'domain' => '', 'secure' => false, 'httponly' => false]`
- `destroy()` - destroy a session
- `get(string $key)` - value getter
- `has(string $key): bool` - check if key exists
- `isSession(): bool` - check if session exists
- `set(string $key, $value)` - key/value setter
- `toArray(): array` - session array getter

## Response

`Lark\Response` is an HTTP response helper.

```php
// set header, status code 200, content-type and send JSON response
app()->response()
	->header('X-Test', 'value')
	->status(\Lark\Response::HTTP_OK)
	->contentType('application/json') // not required when using json()
	->json(['ok' => true]);
// {"ok": true}
```

### Response Methods

- `cacheOff(): Lark\Response` - disable cache using cache-control
- `contentType(string $contentType): Lark\Response` - content-type setter
- `cookie($key, $value, $expires, $path, $domain, $secure, $httpOnly): bool` - cookie setter
- `cookieClear(string $key, string $path = '/'): bool` - remove cookie
- `header(string $key, $value): Lark\Response` - header setter
- `headerClear(string $key): Lark\Response` - remove header key
- `headers(array $headers): Lark\Response` - headers setter using array
- `json($data)` - respond with JSON payload (and content-type `application/json` in headers)
- `redirect(string $location, bool $statusCode301 = false)` - send redirect
- `send($data)` - respond with raw data payload
- `status(int $code): Lark\Response` - response status code setter

## Share

`Lark\Share` is a global key/value helper.

```php
// setter
app()->share('mykey', 'myval');

// getter
$val = app()->share('mykey');
```

Other `Lark\Share` methods: `clear(string $key)`, `has(string $key): bool` and `toArray(): array`.

## Model

`Lark\Model` is a model, entity and validation helper.

```php
namespace App\Model;
class User extends \App\Model
{
	public static function schema(): array
	{
		return [
			'name' => ['string', 'notEmpty'],
			'age' => ['int', 'notEmpty'],
			'isAdmin' => ['bool', 'required', ['default' => false]]
		];
	}
}
```

The `App\Model\User` class can be used to simplify validation and creating an entity.

```php
$user = (new \App\Model\User)->make([
	'name' => 'Bob',
	'age' => 25
]);
var_dump($user); // array(3) { ["name"]=> string(3) "Bob" ["age"]=> int(25) ["isAdmin"]=> bool(false) }

// or an array can be used
$user = (new \App\Model\User)->makeArray([
	['name' => 'Bob', 'age' => 25],
	['name' => 'Jane', 'age' => 21]
]);
```

The `ENTITY_FLAG_PARTIAL` flag can be set to allow missing fields that can be used for partial updates.
```php
$user = (new \App\Model\User)->make([
	'name' => 'Bob'
], ENTITY_FLAG_PARTIAL);
var_dump($user); // array(1) { ["name"]=> string(3) "Bob" }
```

The `ENTITY_FLAG_ID` flag can be set to require any field using the `id` rule.
```php
// schema: ['id' => ['string', 'id'], 'name' => ['string', notEmpty]]
$user = (new \App\Model\User)->make([
	'name' => 'Bob'
], ENTITY_FLAG_ID);
// throws Lark\Validator\ValidatorException:
// Validation failed: "id" must be a string
```
> Multiple entity flags can be set like: `ENTITY_FLAG_ID | ENTITY_FLAG_PARTIAL`

## Store
`Lark\Store` is a store and database helper.
```php
// bootstrap
// setup default MongoDB connection
app()->bind('store.db.connection.default', [
	'hosts' => ['127.0.0.1'],
	'username' => 'test',
	'password' => 'secret',
	// 'replicaSet' => 'rsNameHere'
]);

// ...

// get DB object instance
// syntax: "[storeType].[connection (when not default)].[database].[collection]"
$db = store('db.myDb.myCollection');

// insert documents
$ids = $db->insert([
	'name' => 'test'
], [
	'name' => 'test2'
]); // Array ( [0] => 61240f4fc5140c63343332e2 [1] => 61240f4fc5140c63341232e3 )

// fetch all documents
$docs = $db->find();

// fetch documents
$docs = $db->find(['name' => 'test']);
// Array ( [0] => Array ( [id] => 61240f4fc5140c63343332e2 [name] => test ) )

// fetch single document
$doc = $db->findOne(['name' => 'test']);
// Array ( [id] => 61240f4fc5140c63343332e2 [name] => test )

// update documents
$affected = $db->update(['name' => 'test'], ['name' => 'TEST']); // 1

// delete documents
$affected = $db->delete(['name' => 'TEST']); // 1
```
### Store Types
- `db` - MongoDB
- `es` - Elasticsearch (coming soon)
- `sql` - MySQL / MariaDB (future development)

### Store Methods
- `count(array $filter = [], array $options = []): int` - count documents matching filter
- `delete(array $filter, array $options = []): int` - delete documents matching filter
- `deleteAll(array $options = []): int` - delete all documents
- `deleteIds(array $ids, array $options = []): int` - delete documents by ID
- `deleteOne(array $filter, array $options = []): int` - delete single document matching filter
- `drop(): bool` - drop collection
- `exists(): bool` - check if collection exists
- `find(array $filter = [], array $options = []): array` - find documents matching filter
- `findId($id, array $options = []): ?array` - find document by ID
- `findIds(array $ids, array $options = []): array` - find documents by ID
- `findOne(array $filter = [], array $options = []): ?array` - find single document matching filter
- `has(array $filter, array $options = []): bool` - check if documents matching filter exist
- `hasIds(array $ids, array $options = []): bool` - check if documents with IDs exist
- `insert(array $documents, array $options = []): array` - insert documents
- `insertOne($document, array $options = []): ?string` - insert single document
- `ping(): bool` - ping command
- `replaceBulk(array $documents, array $options = []): int` - bulk replace
- `replaceId($id, $document, array $options = []): int` - replace single document
- `replaceOne(array $filter, $document, array $options = []): int` - replace single document
- `update(array $filter, $update, array $options = []): int` - update documents matching filter
- `updateBulk(array $documents, array $options = []): int` - bulk update
- `updateId($id, $update, array $options = []): int` - update document by ID
- `updateOne(array $filter, $update, array $options = []): int` - update single document matching filter


## Validator

`Lark\Validator` is a validation helper.

```php
$isValid = app()->validator([
	// data
	'name' => 'Bob',
	'age' => 25
], [
	// schema
	'name' => ['string', 'notEmpty'],
	'age' => ['int', 'notNull'],
	'phone' => null, // no type (generic), optional
	'title' => 'string' // string, optional
])->validate(); // true
```

Assertion can be used during validation.

```php
app()->validator([
	'name' => null
], [
	'name' => ['string', 'notNull']
])->assert();
// throws Lark\Validator\ValidatorException:
// Validation failed: "name" must be a string
```

### Validation Types & Rules

Rules `notNull` and `notEmpty`, and sometimes `id`, are rules for all types that do not allow the value to be `null`. 
The rule `voidable` can be used for any fields that can be missing.

- `generic` (no type, default) - any type allowed
	- `notNull` - value cannot be `null`
- `array` (or `arr`) - value can be `array` or `null`
	- `allowed` - array values must be allowed `[allowed => [...]]`
	- `length` - number of array items must be `[length => x]`
	- `max` - array values cannot exceed maximum value of `[max => x]`
	- `min` - array values cannot be lower than minimum value of `[min => x]`
	- `notEmpty` - must be a non-empty `array`
	- `notNull` - must be an `array`
	- `unique` - array values must be unique
- `boolean` (or `bool`) - must be `boolean` or `null`
	- `notNull` - must be `boolean`
- `float` - must be a `float` or `null`
	- `between` - must be between both values `[between => [x, y]]`
	- `max` - must be a maximum value of `[max => x]`
	- `min` - must be a minimum value of `[min => x]`
	- `notEmpty` - must be a non-zero `float`
	- `notNull` - must be a `float`
- `integer` (or `int`) - must be an `integer` or `null`
	- `between` - must be between both values `[between => [x, y]]`
	- `id` - must be an `integer` when `ENTITY_FLAG_ID` flag is set
	- `max` - must be a maximum value of `[max => x]`
	- `min` - must be a minimum value of `[min => x]`
	- `notEmpty` - must be a non-zero `integer`
	- `notNull` - must be an `integer`
- `number` (or `num`) - must be a number or `null`
	- `between` - must be between both values `[between => [x, y]]`
	- `id` - must be a number when `ENTITY_FLAG_ID` flag is set
	- `max` - must be a maximum value of `[max => x]`
	- `min` - must be a minimum value of `[min => x]`
	- `notEmpty` - must be a non-zero number
	- `notNull` - must be a number
- `object` (or `obj`) - must be an `object` or `null`
	- `notEmpty` - must be a non-empty `object`
	- `notNull` - must be an `object`
- `string` (or `str`) - must be a `string` or `null`
	- `allowed` - value must be allowed `[allowed => [...]]`
	- `alnum` - must only contain alphanumeric characters
		- or, must only contain alphanumeric characters and whitespaces `[alnum => true]`
	- `alpha` - must only contain alphabetic characters
		- or, must only contain alphabetic characters and whitespaces `[alpha => true]`
	- `contains` - must contain value `[contains => x]`
		- or, must contain value (case-insensitive) `[contains => [x, true]]`
	- `email` - must be a valid email address
	- `hash` - hashes must be equal (timing attack safe) `[hash => x]`
	- `id` - must be an `string` when `ENTITY_FLAG_ID` flag is set
	- `ipv4` - must be valid IPv4 address
	- `ipv6` - must be valid IPv6 address
	- `json` - must be a valid JSON
	- `length` - length must be number of characters `[length => x]`
	- `match` - value must be a regular expression match `[match => x]`
	- `max` - length must be a maximum number of characters `[max => x]`
	- `min` - length must be a minimum number of characters `[min => x]`
	- `notAllowed` - value must be allowed `[notAllowed => [...]]`
	- `notEmpty` - must be a non-empty `string`
	- `notNull` - must be a `string`
	- `password` - passwords must match `[password => x]`
	- `url` - must be a valid URL
- `timestamp` - must be a timestamp or `null`
	- `required` - must be a timestamp

### Nested Fields

Nested fields can be defined using the `fields` property.

```php
$isValid = app()->validator([
	// data
	'name' => 'Bob',
	'contact' => [
		'email' => 'bob@example.com',
		'phone' => [
			'cell' => '555-5555',
			'office' => '555-6666'
		]
	]
], [
	// schema
	'name' => ['string', 'notEmpty'],
	'contact' => [
		'array',
		'fields' => [
			'email' => ['string', 'email'],
			'phone' => [
				'array',
				'fields' => [
					'cell' => 'string',
					'office' => 'string'
				]
			]
		]
	]
])->validate(); // true
```

### Assert Callback

A callback can be used with the `assert()` method.

```php
app()->validator([
	'name' => null
], [
	'name' => ['string', 'required']
])->assert(function(string $field, string $message, string $model){
	// handle error
	//...

	// return true to halt
	// return false to continue to throw validation exception
	return true;
});
```

### Custom Validation Rule

Custom validation rules can be created.

```php
// validator.rule.[type].[name]
app()->bind('validator.rule.string.beginWithEndWith', \App\Validator\BeginWithEndWith::class);

// App\Validator\MyRule class:
namespace App\Validator;
class BeginWithEndWith extends \Lark\Validator\Rule
{
	private string $beginWith;
	private string $endWith;

	protected string $message = 'must begin with value and end with value';

	public function __construct(string $beginWith, string $endWith)
	{
		$this->beginWith = $beginWith;
		$this->endWith = $endWith;
	}

	public function validate($value): bool
	{
		$beginsWith = substr($value, 0, strlen($this->beginWith));
		$endsWith = substr($value, -(strlen($this->endWith)));

		return $beginsWith === $this->beginWith && $endsWith === $this->endWith;
	}
}

// validation example
app()->validator([
	'alias' => '123testXYZ'
], [
	'alias' => ['string', ['beginWithEndWith' => ['123', 'XYZ']]]
])->validate(); // true
```

It is also possible to override existing rules.

```php
// validator.rule.[type].[name]
// overwrite existing string rule "email"
app()->bind('validator.rule.string.email', '\\App\\Validator\\Email');

// App\Validator\Email class:
namespace App\Validator;
class Email extends \Lark\Validator\TypeString\Email
{
	public function validate($value): bool
	{
		// must be valid email and domain "example.com"
		return parent::validate($value)
			&& preg_match('/@example\.com$/i', $value) === 1;
	}
}

// validation example
app()->validator([
	'email' => 'test@example.com'
], [
	'email' => ['string', 'email']
])->validate(); // true
```

## Filter
`Lark\Filter` is a filter helper.
```php
$cleanStr = filter()->string($str);
```
Filter by array keys.
```php
$arr = ["one" => 1, "two" => 2, "three" => 3];

// exclude filter
print_r(
	filter()->keys($arr, ["two" => 0])
); // Array ( [one] => 1 [three] => 3 )

// include filter
print_r(
	filter()->keys($arr, ["one" => 1, "two" => 1])
); // Array ( [one] => 1 [two] => 2 )
```

### Filter Methods
- `email($value, array $options = []): string` - sanitize value with email filter
- `float($value, array $options = ['flags' => FILTER_FLAG_ALLOW_FRACTION]): float` - sanitize value with float filter
- `integer($value, array $options = []): int` - sanitize value with integer filter
- `keys(array $array, array $filter): array` - filters keys based on include or exclude filter
- `string($value, array $options = ['flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]): string` - sanitize value with string filter
- `url($value, array $options = []): string` - sanitize value with url filter