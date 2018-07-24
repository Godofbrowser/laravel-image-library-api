<?php

namespace App\Exceptions;

use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

class XhrException extends \RuntimeException
{
    public $validations = [];

    public static function runtimeException($message, $httpCode = 422) : XhrException
    {
        return new static($message, $httpCode);
    }

    public static function validationException(
        ValidationException $e,
        $httpCode = Response::HTTP_BAD_REQUEST
    ) : XhrException
    {
        $exception = new static('Failed to pass validation', $httpCode);
        $exception->validations = $e->validator->getMessageBag()->toArray();
        return $exception;
    }

    public static function invalidRequestException($message) : XhrException
    {
        return new static($message);
    }

    public function hasValidationMessages() : bool
    {
        return count($this->validations);
    }

    public function getValidationMessages() : array
    {
        return $this->validations;
    }
}
