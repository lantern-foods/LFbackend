<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            \Log::error($e);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            $errorCode = 500; // Default error code
            $errorMessage = "Oops! A problem was encountered while processing your request. Please try again or contact support for further assistance!"; // Default error message

            if ($e instanceof AuthenticationException) {
                $errorCode = 401;
                $errorMessage="Unauthenticated!";
            } elseif ($e instanceof NotFoundHttpException) {
                $errorCode = 404;
                $errorMessage="Not found!";
            } elseif ($e instanceof HttpException) {
                $errorCode = $e->getStatusCode();

                if ($errorCode === 413) {
                    $errorMessage = "Payload Too Large";
                }elseif($errorCode === 403){
                    $errorMessage = "Unauthorized";
                }
            } elseif ($e instanceof ValidationException) {
                $errorCode = 422;
                $errorMessage = "Unprocessable Entity";
            }

            return response()->json([
                "error" => true,
                "message" => $errorMessage,
                "code" => $errorCode
            ],$errorCode);
        });
    }
}
