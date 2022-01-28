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
 * Store interface
 *
 * @author Shay Anderson
 */
interface StoreInterface
{
	/**
	 * Count documents matching filter
	 *
	 * @param array $filter
	 * @param array $options
	 * @return integer
	 */
	public function count(array $filter = [], array $options = []): int;

	/**
	 * Delete documents matching filter
	 *
	 * @param array $filter
	 * @param array $options
	 * @return integer
	 */
	public function delete(array $filter, array $options = []): int;

	/**
	 * Delete all documents
	 *
	 * @param array $options
	 * @return integer
	 */
	public function deleteAll(array $options = []): int;

	/**
	 * Delete documents by ID
	 *
	 * @param array $ids
	 * @param array $options
	 * @return integer
	 */
	public function deleteIds(array $ids, array $options = []): int;

	/**
	 * Delete single document matching filter
	 *
	 * @param array $filter
	 * @param array $options
	 * @return integer
	 */
	public function deleteOne(array $filter, array $options = []): int;

	/**
	 * Drop collection
	 *
	 * @return boolean
	 */
	public function drop(): bool;

	/**
	 * Check if collection exists
	 *
	 * @return boolean
	 */
	public function exists(): bool;

	/**
	 * Find documents matching filter
	 *
	 * @param array $filter
	 * @param array $options
	 * @return array
	 */
	public function find(array $filter = [], array $options = []): array;

	/**
	 * Find document by ID
	 *
	 * @param string|int $id
	 * @param array $options
	 * @return array|null
	 */
	public function findId($id, array $options = []): ?array;

	/**
	 * Find documents by ID
	 *
	 * @param array $ids
	 * @param array $options
	 * @return array
	 */
	public function findIds(array $ids, array $options = []): array;

	/**
	 * Find single document matching filter
	 *
	 * @param array $filter
	 * @param array $options
	 * @return array|null
	 */
	public function findOne(array $filter = [], array $options = []): ?array;

	/**
	 * Check if documents matching filter exist
	 *
	 * @param array $filter
	 * @param array $options
	 * @return boolean
	 */
	public function has(array $filter, array $options = []): bool;

	/**
	 * Check if documents with IDs exist
	 *
	 * @param array $ids
	 * @param array $options
	 * @return boolean
	 */
	public function hasIds(array $ids, array $options = []): bool;

	/**
	 * Insert documents
	 *
	 * @param array $documents
	 * @param array $options
	 * @return array
	 */
	public function insert(array $documents, array $options = []): array;

	/**
	 * Insert single document
	 *
	 * @param array|object $document
	 * @param array $options
	 * @return string|null
	 */
	public function insertOne($document, array $options = []): ?string;

	/**
	 * Ping command
	 *
	 * @return boolean
	 */
	public function ping(): bool;

	/**
	 * Bulk replace
	 *
	 * @param array $documents
	 * @param array $options
	 * @return integer
	 */
	public function replaceBulk(array $documents, array $options = []): int;

	/**
	 * Replace document by ID
	 *
	 * @param string|int $id
	 * @param array|object $document
	 * @param array $options
	 * @return integer
	 */
	public function replaceId($id, $document, array $options = []): int;

	/**
	 * Replace single document
	 *
	 * @param array $filter
	 * @param array|object $document
	 * @param array $options
	 * @return integer
	 */
	public function replaceOne(array $filter, $document, array $options = []): int;

	/**
	 * Update documents matching filter
	 *
	 * @param array $filter
	 * @param array|object $update
	 * @param array $options
	 * @return integer
	 */
	public function update(array $filter, $update, array $options = []): int;

	/**
	 * Bulk update
	 *
	 * @param array $documents
	 * @param array $options
	 * @return integer
	 */
	public function updateBulk(array $documents, array $options = []): int;

	/**
	 * Update document by ID
	 *
	 * @param string|int $id
	 * @param array|object $update
	 * @param array $options
	 * @return integer
	 */
	public function updateId($id, $update, array $options = []): int;

	/**
	 * Update single document matching filter
	 *
	 * @param array $filter
	 * @param array|object $update
	 * @param array $options
	 * @return integer
	 */
	public function updateOne(array $filter, $update, array $options = []): int;
}
