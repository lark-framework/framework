<?php
/**
 * Lark Framework
 *
 * @copyright Shay Anderson <https://www.shayanderson.com>
 * @license MIT License <https://github.com/lark-framework/framework/blob/master/LICENSE.md>
 * @link <https://github.com/lark-framework/framework>
*/
declare(strict_types=1);

namespace Lark\Store;

use Lark\Binding;
use Lark\Validator;
use Lark\Validator\ValidatorException;
use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use MongoDB\Driver\Command;
use MongoDB\Model\BSONDocument;

/**
 * DB store
 *
 * @author Shay Anderson
 */
class DB extends \Lark\Factory\Singleton implements \Lark\StoreInterface
{
	/**
	 * MongoDB client
	 *
	 * @var Client
	 */
	private static Client $client;

	/**
	 * Collection name
	 *
	 * @var string
	 */
	private static string $collection;

	/**
	 * Connection ID
	 *
	 * @var string|null
	 */
	private static ?string $connectionId;

	/**
	 * Default connection ID
	 *
	 * @var string
	 */
	private static string $connectionIdDefault;

	/**
	 * Database name
	 *
	 * @var string
	 */
	private static string $database;

	/**
	 * Options
	 *
	 * @var array
	 */
	private static array $options = [];

	/**
	 * Bulk write
	 *
	 * @param string $operation
	 * @param array $documents
	 * @param array $options
	 * @return integer
	 */
	private function bulkWrite(string $operation, array $documents, array $options): int
	{
		self::convertInputIdToObjectIdArray($documents);
		$ops = [];

		$writeOptions = ['ordered' => true];
		if (array_key_exists('writeOptions', $options))
		{
			$writeOptions = $options['writeOptions'];
			unset($options['writeOptions']);
		}

		foreach ($documents as $doc)
		{
			if (is_object($doc))
			{
				$doc = (array)$doc;
			}

			if (!isset($doc['_id']))
			{
				throw new StoreException('Bulk write method requires ID for all documents');
			}

			$id = $doc['_id'];
			unset($doc['_id']);

			$ops[] = [
				$operation => [
					['_id' => $id], // filter
					($operation === 'updateOne'
						? ['$set' => $doc] // update
						: $doc // other
					),
					$options
				]
			];
		}

		self::log(__METHOD__, [
			'operation' => $operation,
			'operations' => $ops,
			'options' => $options,
			'writeOptions' => $writeOptions,
			'documents' => $documents
		]);

		if (
			($res = $this->collection()->bulkWrite($ops, $writeOptions))
			&& ($res = $res->getModifiedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}

	/**
	 * Client getter
	 *
	 * @param string $database
	 * @param string $collection
	 * @param string|null $connectionId
	 * @return self
	 */
	public function client(string $database, string $collection, string $connectionId = null): self
	{
		self::$database = $database;
		self::$collection = $collection;
		self::$connectionId = $connectionId;
		self::$client = self::connection($connectionId);

		if (!self::$options) // init
		{
			$options = Binding::get('store.db.options');
			self::$options = ($options ?? []) + [
				'find.limit' => 10_000,
				#todo: 'db.deny' => ['admin', 'config', 'local']
				'debug.log' => false
			];
		}

		return $this;
	}

	/**
	 * Collection object getter
	 *
	 * @return \MongoDb\Collection
	 */
	public function collection(): \MongoDb\Collection
	{
		return $this->database()->{self::$collection};
	}

	/**
	 * Connection client getter
	 *
	 * @param string|null $connectionId
	 * @return Client
	 */
	private static function &connection(?string $connectionId): Client
	{
		static $cache;
		static $clients = [];

		if ($cache === null) // init cache
		{
			$cache = Binding::get('store.db');
			if (is_array($cache['connection']))
			{
				self::$connectionIdDefault = array_key_first($cache['connection']);
			}
		}

		if (!self::$connectionIdDefault)
		{
			throw new StoreException('No store "db" connections found');
		}

		$connectionId = $connectionId === null ? self::$connectionIdDefault : $connectionId;

		if (isset($clients[$connectionId]))
		{
			return $clients[$connectionId];
		}

		// init connection
		$conn = null;
		if (isset($cache['connection'][$connectionId]))
		{
			try
			{
				$conn = $cache['connection'][$connectionId];
				// validate
				(new Validator($conn, [
					'hosts' => ['array', 'notEmpty'],
					'username' => ['string', 'notEmpty'],
					'password' => ['string', 'notNull'],
					'replicaSet' => ['string', 'notNull', 'voidable']
				]))->assert();
			}
			catch (ValidatorException $ex)
			{
				throw new StoreException('Store "db" invalid connection "' . $connectionId . '": '
					. $ex->getMessage());
			}
		}

		if (!$conn)
		{
			throw new StoreException('No store "db" connection found for ID "' . $connectionId . '"');
		}

		$hosts = $conn['hosts'];
		unset($conn['hosts']);

		$clients[$connectionId] = new Client('mongodb://' . implode(',', $hosts), $conn);

		return $clients[$connectionId];
	}

	private static function &convertBsonDocToArray(?BSONDocument $doc): ?array
	{
		if (!$doc)
		{
			$doc = null;
			return $doc;
		}

		$doc = &self::convertIteratorToArrayRecursive($doc);

		// convert [_id => ObjectId] to [id => string]
		if (isset($doc['_id']) && $doc['_id'] instanceof ObjectId)
		{
			$doc = ['id' => $doc['_id']->__toString()] + $doc;
			unset($doc['_id']);
		}

		return $doc;
	}

	private static function &convertIteratorToArrayRecursive($iterator): array
	{
		$a = iterator_to_array($iterator);

		foreach ($a as $k => $v)
		{
			if (is_iterable($v))
			{
				$a[$k] = self::convertIteratorToArrayRecursive($v);
			}
		}

		return $a;
	}

	private static function &convertCursorToArray(\MongoDB\Driver\Cursor $cursor): array
	{
		$a = [];

		foreach ($cursor as $o)
		{
			$a[] = &self::convertBsonDocToArray($o);
		}

		return $a;
	}

	private static function &convertIdsToObjectIds(array &$ids): array
	{
		foreach ($ids as &$id)
		{
			$id = self::convertIdToObjectId($id);
		}

		return $ids;
	}

	private static function convertIdToObjectId(?string $id)
	{
		if (!is_string($id) || strlen($id) !== 24)
		{
			return $id;
		}

		return new ObjectId($id);
	}

	private static function convertInputIdToObjectId(&$document): void
	{
		if (is_array($document))
		{
			if (isset($document['id']) || array_key_exists('id', $document))
			{
				$document['_id'] = self::convertIdToObjectId((string)$document['id']);
				unset($document['id']);
			}
		}
		else if (is_object($document))
		{
			if (property_exists($document, 'id'))
			{
				$document->_id = self::convertIdToObjectId((string)$document->id);
				unset($document->id);
			}
		}
	}

	private static function convertInputIdToObjectIdArray(array &$documents): void
	{
		foreach ($documents as &$doc)
		{
			self::convertInputIdToObjectId($doc);
		}
	}

	/**
	 * Convert BSON ObjectId to string
	 *
	 * @param ObjectId|string $objectId
	 * @return string|null
	 */
	private static function convertObjectIdToString($objectId): ?string
	{
		if ($objectId instanceof ObjectId)
		{
			return $objectId->__toString();
		}

		if (is_scalar($objectId))
		{
			return (string)$objectId;
		}

		return null;
	}

	public function count(array $filter = [], array $options = []): int
	{
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'options' => $options
		]);

		return $this->collection()->count($filter, $options);
	}

	public function database(): \MongoDB\Database
	{
		return self::$client->{self::$database};
	}

	public function delete(array $filter, array $options = []): int
	{
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'options' => $options
		]);

		if (empty($filter))
		{
			throw new StoreException('Filter cannot be empty for this method');
		}

		if (
			($res = $this->collection()->deleteMany($filter, $options))
			&& ($res = $res->getDeletedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}

	public function deleteAll(array $options = []): int
	{
		self::log(__METHOD__, [
			'options' => $options
		]);

		if (
			($res = $this->collection()->deleteMany([], $options))
			&& ($res = $res->getDeletedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}

	public function deleteIds(array $ids, array $options = []): int
	{
		self::log(__METHOD__, [
			'ids' => $ids,
			'options' => $options
		]);

		if (empty($ids))
		{
			return 0;
		}

		return $this->delete([
			'_id' => [
				'$in' => self::convertIdsToObjectIds($ids)
			]
		], $options);
	}

	public function deleteOne(array $filter, array $options = []): int
	{
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'options' => $options
		]);

		if (empty($filter))
		{
			throw new StoreException('Filter cannot be empty for this method');
		}

		if (
			($res = $this->collection()->deleteOne($filter, $options))
			&& ($res = $res->getDeletedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}

	public function drop(): bool
	{
		$doc = $this->database()->dropCollection(self::$collection);
		return $doc->ok == 1;
	}

	/**
	 * Execute command
	 *
	 * @param \MongoDB\Driver\Command $command
	 * @param string|null $database
	 * @return \MongoDB\Driver\Cursor
	 */
	public function executeCommand(
		Command $command,
		string $database = null
	): \MongoDB\Driver\Cursor
	{
		return self::$client->getManager()->executeCommand(
			$database ? $database : self::$database,
			$command
		);
	}

	public function exists(): bool
	{
		return in_array(self::$collection, $this->getCollections());
	}

	public function find(array $filter = [], array $options = []): array
	{
		self::findOptions($options);
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'options' => $options
		]);

		return self::convertCursorToArray(
			$this->collection()->find($filter, $options)
		);
	}

	public function findId($id, array $options = []): ?array
	{
		self::log(__METHOD__, [
			'id' => $id,
			'options' => $options
		]);

		return $this->findOne(['_id' => self::convertIdToObjectId($id)], $options);
	}

	public function findIds(array $ids, array $options = []): array
	{
		self::log(__METHOD__, [
			'ids' => $ids,
			'options' => $options
		]);

		if (empty($ids))
		{
			return [];
		}

		return $this->find([
			'_id' => [
				'$in' => self::convertIdsToObjectIds($ids)
			]
		], $options);
	}

	public function findOne(array $filter = [], array $options = []): ?array
	{
		self::findOptions($options);
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'options' => $options
		]);

		return self::convertBsonDocToArray(
			$this->collection()->findOne($filter, $options)
		);
	}

	private static function findOptions(array &$options): void
	{
		if (!isset($options['limit']) && self::$options['find.limit'] > 0)
		{
			$options['limit'] = self::$options['find.limit'];
		}
	}

	/**
	 * Database collection names getter
	 *
	 * @return array
	 */
	public function &getCollections(): array
	{
		$a = [];

		// workaround for bug in db->listCollections()
		$cursor = $this->executeCommand(
			new Command([
				'listCollections' => 1,
				'nameOnly' => 1,
				'authorizedCollections' => 1

			])
		);

		if ($cursor)
		{
			foreach ($cursor->toArray() as $v)
			{
				$a[] = $v->name;
			}
		}

		return $a;
	}

	public function has(array $filter, array $options = []): bool
	{
		self::log(__METHOD__, [
			'filter' => $filter,
			'options' => $options
		]);

		return $this->count($filter, $options) > 0;
	}

	public function hasIds(array $ids, array $options = []): bool
	{
		self::log(__METHOD__, [
			'ids' => $ids,
			'options' => $options
		]);

		if (empty($ids))
		{
			return false;
		}

		return $this->has([
			'_id' => [
				'$in' => self::convertIdsToObjectIds($ids)
			]
		]);
	}

	// @return array (IDs)
	public function insert(array $documents, array $options = []): array
	{
		self::log(__METHOD__, [
			'documents' => $documents,
			'options' => $options
		]);

		if (empty($documents)) // avoid MongoDB exception "$documents is empty"
		{
			return [];
		}

		self::convertInputIdToObjectIdArray($documents);
		$ids = [];

		if (
			($res = $this->collection()->insertMany($documents, $options))
			&& ($res = $res->getInsertedIds())
		)
		{
			foreach ($res as $v)
			{
				$ids[] = self::convertObjectIdToString($v);
			}
		}

		return $ids;
	}

	public function insertOne($document, array $options = []): ?string
	{
		self::convertInputIdToObjectId($document);
		self::log(__METHOD__, [
			'document' => $document,
			'options' => $options
		]);

		if (empty($document)) // empty document
		{
			return null;
		}

		if (
			($res = $this->collection()->insertOne($document, $options))
			&& ($res = $res->getInsertedId())
		)
		{
			return self::convertObjectIdToString($res);
		}

		return null;
	}

	private static function log(string $message, array $context): void
	{
		if (!self::$options['debug.log'])
		{
			return;
		}

		$context = [
			'db' => self::$database,
			'collection' => self::$collection,
			'connectionId' => self::$connectionId
		] + $context;

		(new \Lark\Logger('lark.store.db'))->debug($message, $context);
	}

	public function objectId(?string $id)
	{
		return self::convertIdToObjectId($id);
	}

	public function ping(): bool
	{
		try
		{
			$cursor = $this->executeCommand(
				new Command(['ping' => 1]),
				'admin'
			);

			return ($cursor->toArray()[0]->ok ?? null) == 1;
		}
		catch (\MongoDB\Driver\Exception\ConnectionTimeoutException $ex)
		{
			self::log(__METHOD__, [
				'connectionTimeout' => true,
				'exceptionMessage' => $ex->getMessage()
			]);
		}

		return false;
	}

	public function replaceBulk(array $documents, array $options = []): int
	{
		self::log(__METHOD__, [
			'documents' => $documents,
			'options' => $options
		]);

		return $this->bulkWrite('replaceOne', $documents, $options);
	}

	public function replaceId($id, $document, array $options = []): int
	{
		self::log(__METHOD__, [
			'id' => $id,
			'document' => $document,
			'options' => $options
		]);

		return $this->replaceOne(['_id' => self::convertIdToObjectId($id)], $document, $options);
	}

	public function replaceOne(array $filter, $document, array $options = []): int
	{
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'document' => $document,
			'options' => $options
		]);

		if (
			($res = $this->collection()->replaceOne($filter, $document, $options))
			&& ($res = $res->getModifiedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}

	public function update(array $filter, $update, array $options = []): int
	{
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'update' => $update,
			'options' => $options
		]);

		// check for empty update
		if (
			is_array($update) && empty($update)
			|| is_object($update) && empty(get_object_vars($update))
		)
		{
			return 0;
		}

		if (
			($res = $this->collection()->updateMany($filter, [
				'$set' => $update
			], $options))
			&& ($res = $res->getModifiedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}

	public function updateBulk(array $documents, array $options = []): int
	{
		self::log(__METHOD__, [
			'documents' => $documents,
			'options' => $options
		]);

		return $this->bulkWrite('updateOne', $documents, $options);
	}

	public function updateId($id, $update, array $options = []): int
	{
		self::log(__METHOD__, [
			'id' => $id,
			'update' => $update,
			'options' => $options
		]);

		// check for empty update
		if (
			is_array($update) && empty($update)
			|| is_object($update) && empty(get_object_vars($update))
		)
		{
			return 0;
		}

		return $this->updateOne(['_id' => self::convertIdToObjectId($id)], $update, $options);
	}

	public function updateOne(array $filter, $update, array $options = []): int
	{
		self::convertInputIdToObjectId($filter);
		self::log(__METHOD__, [
			'filter' => $filter,
			'update' => $update,
			'options' => $options
		]);

		// check for empty update
		if (
			is_array($update) && empty($update)
			|| is_object($update) && empty(get_object_vars($update))
		)
		{
			return 0;
		}

		if (
			($res = $this->collection()->updateOne($filter, [
				'$set' => $update
			], $options))
			&& ($res = $res->getModifiedCount())
		)
		{
			return (int)$res;
		}

		return 0;
	}
}
