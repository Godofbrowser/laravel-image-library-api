<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class AppException extends \RuntimeException
{
    public $validations = [];

    /**
     * @param $message
     * @param int $httpCode
     *
     * @return AppException
     */
    public static function runtimeException($message, $httpCode = Response::HTTP_BAD_REQUEST): AppException
    {
        return new static($message, $httpCode);
    }

	public static function badMethodCallException($message, $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR)
	{
		return new static($message, $httpCode);
	}

    public static function upgradeRequiredException(
    	$message = 'An upgrade is required to use this feature.',
		$httpCode = Response::HTTP_PAYMENT_REQUIRED
	): AppException
    {
        return new static($message, $httpCode);
    }

    /**
     * @param ValidationException $e
     * @param int $httpCode
     *
	 * @return AppException
     */
    public static function validationException(
        ValidationException $e,
        $httpCode = Response::HTTP_UNPROCESSABLE_ENTITY
	): AppException
    {
        $exception = new static('Failed to pass validation', $httpCode);
        $exception->validations = $e->validator->getMessageBag()->toArray();
        return $exception;
    }

	/**
     * @return bool
     */
    public function hasValidationMessages(): bool
    {
        return isset($this->validations[0]);
    }

    /**
     * @return array
     */
    public function getValidationMessages(): array
    {
        return $this->validations;
    }
}
