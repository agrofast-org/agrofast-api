<?php

namespace App\Http\Middleware;

use App\Exception\InvalidFormException;
use App\Exception\InvalidRequestException;
use App\Models\System\ErrorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseTransaction
{
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

            if (isset($response->exception) && !($response->exception instanceof InvalidFormException || $response->exception instanceof InvalidRequestException)) {
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