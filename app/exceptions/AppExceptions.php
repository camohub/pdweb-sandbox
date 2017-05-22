<?php

namespace App\Exceptions;

use Nette;


/**
 * Class DuplicateEntryException
 * @package App\Exceptions
 */
class DuplicateEntryException extends \Exception
{
	// Try to insert duplicate value to fields with unique key
}

/**
 * Class AccessDeniedException
 * @package App\Exceptions
 */
class AccessDeniedException extends \Exception
{
	// //User have not premission to view or do something
}

/**
 * Class InvalidArgumentException
 * @package App\Exceptions
 */
class InvalidArgumentException extends \Exception
{
	// Parameter suplied as an argument is wrong (typehint/range)
}

/**
 * Class ConfirmationEmailException
 * @package App\Exceptions
 */
class ConfirmationEmailException extends \Exception
{
	// Something goes wrong with confirmation emails. (Ie. users acount is active, but email is not confirmed.)
}

/**
 * Class GeneralException
 * @package App\Exceptions
 */
class GeneralException extends \Exception
{
	// Something goes wrong but we don't know what.
}

/**
 * Class ItemNotFoundException
 * @package App\Exceptions
 */
class ItemNotFoundException extends \Exception
{
	// Item was not found.
	public $code = 404;
}

/**
 * Class ItemNotFoundException
 * @package App\Exceptions
 */
class CreateDirectoryException extends \Exception
{
	// Something goes wrong with mkdir() call.
}







