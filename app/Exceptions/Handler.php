<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Response;
use Laravel\Passport\Exceptions\MissingScopeException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
		ApiException::class,
		AppException::class,
		XhrException::class,
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
		if ($exception instanceof TokenMismatchException){
			return $this->TokenMismatchExceptionHandler($request, $exception);
		}elseif ($exception instanceof AppException){
			return $this->ConvertAppExceptionToResponse($request, $exception);
		}elseif ($exception instanceof ApiException){
			return $this->ConvertApiExceptionToResponse($request, $exception);
		}elseif ($exception instanceof XhrException){
			return $this->ConvertXhrExceptionToResponse($request, $exception);
		}

        return parent::render($request, $exception);
    }


	protected function ConvertApiExceptionToResponse($request, ApiException $exception)
	{
		return Response::json(['error' => $exception->getMessage()], $exception->getCode());
	}

	protected function ConvertHttpExceptionToApiResponse($request, Exception $exception)
	{
		$statusCode = $exception->getCode() > 0 ? $exception->getCode() : 400;
		$message = $exception->getMessage();

		if ($exception instanceof MissingScopeException){
			$message = 'Scope [' . implode(',', $exception->scopes()) . '] authorization required';
		}

		return Response::json([
			'status' => 'error',
			'info' => $message
		], $statusCode);
	}

	protected function ConvertXhrExceptionToResponse($request, XhrException $exception)
	{
		return Response::json(['error' => $exception->getMessage()], $exception->getCode());
	}

	protected function ConvertAppExceptionToResponse(Request $request, AppException $exception)
	{
		$status = $exception->getCode();

		if ($request->expectsJson()){
			// Render errors for XHR request
			$data = ['error' => $exception->getMessage()];

			return Response::json($data, $status);
		}
		else {

			if (view()->exists("errors.$status")) {
				return response()->view("errors.$status", [
					'exception' => $exception
				], $status);
			} else {
				return Response::view('errors.exception', [
					'message' => $exception->getMessage(),
					'code' => $status
				], $status);
			}
		}
	}

	private function TokenMismatchExceptionHandler(Request $request, $exception)
	{
		if ($request->expectsJson())
			return response()->json(['error' => 'Token Expired/Invalid'], 401);
		return redirect()->back();
	}
}
