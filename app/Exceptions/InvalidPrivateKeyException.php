<?php

namespace App\Exceptions;

use Exception;

class InvalidPrivateKeyException extends Exception
{
	public function __construct($message="The private key for the remote host is invalid") {
		parent::__construct($message);
	}
}