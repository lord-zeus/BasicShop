<?php

namespace App\Exceptions;

use App\Traits\APIResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use APIResponse;
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
        //
    }

    public function render($request, Throwable $exception)
    {
        if($exception instanceof HttpException){
            $code = $exception->getStatusCode();
            $check_message = $exception->getMessage();
            if(!empty($check_message)){
                $message = $exception->getMessage();
                return $this->errorMessage($message, $code);
            }
            else
                $message = Response::$statusTexts[$code];
            return $this->errorResponse($message, $code);
        }
        if($exception instanceof ModelNotFoundException){
            $model = strtolower(class_basename($exception->getModel()));
            return $this->errorResponse("Does not Exist Any Instance of {$model} with the given id", Response::HTTP_NOT_FOUND);
        }

        if($exception instanceof AuthorizationException){
            return $this->errorResponse($exception->getMessage(), Response::HTTP_FORBIDDEN);
        }

        if($exception instanceof AuthenticationException){
            return $this->errorResponse($exception->getMessage(), Response::HTTP_UNAUTHORIZED);
        }

        if($exception instanceof ValidationException){
            $error = $exception->validator->errors()->getMessages();
            return $this->errorResponse($error, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if(env('APP_DEBUG')){
            return parent::render($request, $exception);
        }

        return  $this->errorResponse('UnExpected Error Auth Micro Service. Try again Later', Response::HTTP_INTERNAL_SERVER_ERROR);
    }

}
