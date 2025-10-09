<?php

namespace App\Http\Middleware;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Models\System\ErrorLog;
use App\Support\Traits\IgnoresExceptionOnTransaction;
use Dotenv\Exception\ValidationException;
use Illuminate\Validation\ValidationException as ValidationValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseTransaction
{
    use IgnoresExceptionOnTransaction;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        DB::beginTransaction();

        try {
            $response = $next($request);

            if (isset($response->exception) && !$this->shouldIgnoreException($response->exception)) {
                DB::rollBack();
            } else {
                DB::commit();
            }

            return $response;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}