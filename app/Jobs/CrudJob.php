<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CrudJob implements ShouldQueue
{
    use Queueable;
    public $tries = 1;
    // public $backoff = 5;

    protected string $serviceClass;
    protected string $metodo;
    protected array $params;
    public string $uuid;

    public function __construct(string $serviceClass, string $metodo, array $params, string $uuid)
    {
        $this->serviceClass = $serviceClass;
        $this->metodo = $metodo;
        $this->params = $params;
        $this->uuid = $uuid;
    }

    public function handle(): void
    {
        $servicio = app()->make($this->serviceClass);

        //TODO: Limpiar este codigo
        if (is_array($this->params)) {
            $respuesta = call_user_func_array([$servicio, $this->metodo], $this->params);

        } else {
            $respuesta = $servicio->{$this->metodo}();
        }

        // sleep(2); 

        Cache::put("t:$this->uuid", $respuesta, config('cache.tiempo_cache'));
        // broadcast(new JobFinalizado($this->datos));
    }

    public function failed(\Throwable $exception): void
    {
        Cache::put("t_fallidas:$this->uuid", "fallido: \n" . $exception->getMessage(), config('cache.tiempo_cache_error'));
        // log()->error("Error al ejecutar el job '{$this->serviceClass}::{$this->metodo}': " . $exception->getMessage());
    }
}
