<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        $this->renderable(function (Throwable $exception) {
            $return = [
                'code' => 500,
                'message' => $exception->getMessage(),
                'result' => NULL
            ];
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                $return['code'] = 422;
                $return['message'] = $exception->errors();
            } else if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $model = $exception->getModel();
                $className = strtolower(last(explode('\\', $model)));
                $return['code'] = 404;
                $return['message'] = 'Data ' . $className . ' not found';
            } else if ($exception instanceof \Illuminate\Validation\UnauthorizedException || $exception instanceof \Laravel\Passport\Exceptions\MissingScopeException) {
                $return['code'] = 401;
                $return['message'] = 'Unauthorize';
            } else if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException || $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                $return['code'] = 404;
                $return['message'] = 'Route not found';
            }
            return response()->json($return, $return['code']);
        });
    }
}
