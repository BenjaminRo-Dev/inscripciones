<?php

namespace App\Services;

use App\Jobs\CrudJob;
use Illuminate\Support\Facades\Cache;
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

        CrudJob::dispatch($serviceClass, $metodo, $params, $uuid)->onQueue($this->rabbitMQService->getColaCorta());

        Cache::put("t:$uuid", [
            'estado' => 'procesando',
        ], config('cache.tiempo_cache'));

        return response()->json([
            'message' => 'Operacion en proceso',
            'url' => url("api/inscripciones/estado/$uuid"),
            'transaction_id' => $uuid,
            'status' => 'procesando'
        ], 202);
    }


    // public function encolar(string $serviceClass, string $metodo, ...$params)
    // {
    //     if (stripos($metodo, 'mostrar') === false) {
    //         $idempotencia = $this->existeIdempotencia($params);
    //         if ($idempotencia) {
    //             return $idempotencia;
    //         }
    //     }

    //     $uuid = Str::uuid()->toString();

    //     $userId = request()->header('user-id', 'anonimo');
    //     $llaveIdempotencia = md5($userId . json_encode($params));

    //     CrudJob::dispatch($serviceClass, $metodo, $params, $uuid, $llaveIdempotencia)
    //         ->onQueue($this->rabbitMQService->getColaCorta());

    //     $this->guardarIdempotencia($params, $uuid);

    //     return response()->json([
    //         'message' => 'Operacion en proceso',
    //         'url' => url("api/inscripciones/estado/$uuid"),
    //         // 'transaction_id' => $uuid,
    //         'status' => 'procesando'
    //     ], 202);
    // }

    private function existeIdempotencia(array $datos)
    {
        $userId = request()->header('user-id', 'anonimo');
        $llaveIdempotencia = md5($userId . json_encode($datos));

        if (Cache::has("idem:$llaveIdempotencia")) {
            $uuid = Cache::get("idem:$llaveIdempotencia");
            return response()->json([
                'message' => 'Operacion ya en proceso',
                'url' => url("api/inscripciones/estado/$uuid"),
                'transaction_id' => $uuid,
                'status' => Cache::get("t:$uuid")
            ], 202);
        }

        return null;
    }

    private function guardarIdempotencia(array $datos, string $uuid)
    {
        $userId = request()->header('user-id', 'anonimo');
        $llaveIdempotencia = md5($userId . json_encode($datos));

        Cache::put("t:$uuid", "procesando", config('cache.tiempo_cache'));
        Cache::put("idem:$llaveIdempotencia", $uuid, config('cache.tiempo_cache'));
    }

}