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

/**
 * Route not found exception
 *
 * @author Shay Anderson
 */
class NotFoundException extends \Lark\Exception
{
	protected $code = 404;
}
