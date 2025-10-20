<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use function Illuminate\Log\log;

class CrudJob implements ShouldQueue
{
    use Queueable;
    public $tries = 1;
    // public $backoff = 5;

    protected string $serviceClass;
    protected string $metodo;
    protected array $params;
    public string $uuid;
    protected ?string $idemKey = null;

    public function __construct(string $serviceClass, string $metodo, array $params, string $uuid, ?string $idemKey = null)
    {
        $this->serviceClass = $serviceClass;
        $this->metodo = $metodo;
        $this->params = $params;
        $this->uuid = $uuid;
        $this->idemKey = $idemKey;
    }

    public function handle(): void
    {
        $servicio = app()->make($this->serviceClass);
        $respuesta = call_user_func_array([$servicio, $this->metodo], $this->params);
        // sleep(2); 

        Cache::put("t:$this->uuid", [
            'estado' => 'procesado',
            'datos' => $respuesta,
        ], config('cache.tiempo_cache'));
        // broadcast(new JobFinalizado($this->datos));
    }

    public function failed(\Throwable $exception): void
    {
        // Cache::put("t_fallidas:$this->uuid", "fallido: \n" . $exception->getMessage(), config('cache.tiempo_cache_error'));
        Cache::put("t:$this->uuid", [
            'estado' => 'error',
            'error' => $exception->getMessage(),
        ], config('cache.tiempo_cache'));
        
        Cache::put("t:$this->uuid", "ERROR al procesar: \n " .  $exception->getMessage(), config('cache.tiempo_cache'));

        if (!empty($this->idemKey)) {
            Cache::forget("idem:{$this->idemKey}");
        }

        log()->error("Error al ejecutar el job '{$this->serviceClass}::{$this->metodo}': " . $exception->getMessage());
    }

}
