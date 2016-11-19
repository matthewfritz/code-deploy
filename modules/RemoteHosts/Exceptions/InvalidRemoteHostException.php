<?php

namespace RemoteHosts\Exceptions;

use Exception;

class InvalidRemoteHostException extends Exception
{
	public function __construct($message="The remote host is invalid") {
		parent::__construct($message);
	}
}