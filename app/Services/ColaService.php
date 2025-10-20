<?php

namespace App\Services;

use App\Jobs\CrudJob;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ColaService
{

    public function __construct(protected RabbitMQService $rabbitMQService)
    {
        $this->rabbitMQService = $rabbitMQService;
    }

    public function encolar(string $serviceClass, string $metodo, ...$params)
    {
        $uuid = Str::uuid()->toString();
        $params[0]['uuid'] = $uuid;

        // return $params;

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

    public function asignarWorkers(string $cola, int $workers)
    {
        if (!$this->rabbitMQService->existeCola($cola)) {
            $this->rabbitMQService->crearCola($cola);
            // return response()->json(['error' => "La cola '{$cola}' no existe en RabbitMQ."], 404);
        }

        $workersAsignados = Artisan::call('crear-workers', [
            'cola' => $cola,
            'cant' => $workers
        ]);

        if ($workersAsignados !== 0) {
            return response()->json(['error' => "Error al asignar workers a la cola '{$cola}'."], 500);
        }

        return response()->json(['success' => "La cola '{$cola}' ahora tiene {$workers} workers asignados."], 200);
    }

    public function estadoHilos()
    {
        try {
            $comando = new Process([
                'supervisorctl',
                '-c',
                '/var/www/html/mi_config/supervisord.conf',
                'status'
            ]);

            $comando->run();

            $errorOutput = trim($comando->getErrorOutput());
            $output = trim($comando->getOutput());

            if ($comando->getExitCode() !== 0 && !empty($errorOutput)) {
                Log::error("Error ejecutando supervisorctl: " . $errorOutput);
                return response()->json([
                    'error' => 'No se pudo ejecutar el comando supervisorctl.',
                    'detalles' => $errorOutput
                ], 500);
            }

            return response()->json([
                'estado_hilos' => explode("\n", $output)
            ]);
        } catch (\Throwable $e) {
            Log::error("Error ejecutando supervisorctl: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'No se pudo ejecutar el comando supervisorctl.',
                'detalles' => $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstadoHilo(string $accion, string $hilo)
    {
        $comando = new Process([
            'supervisorctl',
            '-c', '/var/www/html/mi_config/supervisord.conf',
            $accion,
            $hilo
        ]);

        $comando->run();

        if (!$comando->isSuccessful()) {
            Log::error("Error ejecutando supervisorctl: " . $comando->getErrorOutput());
            throw new ProcessFailedException($comando);
        }

        return response()->json([
            'accion' => $accion,
            'hilo' => $hilo,
            'detalles' => explode("\n", trim($comando->getOutput()))
        ]);
    }
    
    public function eliminarCola(string $nombreCola)
    {
        try {
            if($this->rabbitMQService->getLongitud($nombreCola) === 0){

                Artisan::call('eliminar-workers', [
                    'cola' => $nombreCola,
                ]);

                $this->rabbitMQService->eliminarCola($nombreCola);

                return response()->json([
                    'success' => "La cola '{$nombreCola}' ha sido eliminada."
                ], 200);

            }

            return response()->json([
                'info' => "La cola '{$nombreCola}' no puede ser eliminada porque tiene trabajos por procesar."  
            ], 200);
            
        } catch (\Exception $e) {
            Log::error("Error eliminando la cola '{$nombreCola}': " . $e->getMessage(), ['exception' => $e]);
            
            return response()->json([
                'error' => "No se pudo eliminar la cola '{$nombreCola}'.",
                'detalles' => $e->getMessage()
            ], 500);   
        }
    }

}