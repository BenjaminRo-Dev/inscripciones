<?php

namespace App\Services;

use App\Jobs\CrudJob;
use App\Jobs\CrudLoteJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ColaService
{

    public function __construct(protected RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    public function encolar(string $serviceClass, string $metodo, ...$params)
    {
        $uuid = Str::uuid()->toString();

        // CrudJob::dispatch($serviceClass, $metodo, $params, $uuid)->onQueue($this->rabbitMQService->getColaCorta());
        CrudJob::dispatch($serviceClass, $metodo, $params, $uuid);


        Cache::put("t:$uuid", "procesando", config('cache.tiempo_cache'));

        return response()->json([
            'message' => 'Operacion en proceso',
            'url' => url("api/estado/$uuid"),
            'transaction_id' => $uuid,
            'status' => 'procesando'
        ], 202);
    }

}