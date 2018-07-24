<?php

namespace App\Exceptions;


class AppException extends \RuntimeException
{
	public static function runtimeException($message = 'Request not completed', $httpCode = 500) : self
	{
		return new static($message, $httpCode);
	}

	public static function invalidRequestException($message = 'Invalid request', $httpCode = 422) : self
	{
		return new static($message, $httpCode);
	}
}
