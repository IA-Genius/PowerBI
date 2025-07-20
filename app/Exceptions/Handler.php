<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Una lista de los tipos de excepciones que no se deben reportar.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Una lista de las entradas que nunca se deben mostrar en errores de validación.
     *
     * @var array<int, string>
     */
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    /**
     * Reportar o registrar una excepción.
     */
    public function register(): void
    {
        // Puedes registrar reportes personalizados aquí si los necesitas
    }

    /**
     * Redirige errores personalizados
     */
    public function render($request, Throwable $exception)
    {
        // Si no está autenticado → login
        if ($exception instanceof AuthenticationException) {
            return redirect()->route('login');
        }

        // Si está autenticado pero no tiene permiso → dashboard
        if (
            $exception instanceof AuthorizationException ||
            ($exception instanceof HttpException && $exception->getStatusCode() === 403)
        ) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para acceder a esta sección.');
        }

        return parent::render($request, $exception);
    }
}
