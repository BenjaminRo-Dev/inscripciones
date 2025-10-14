<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EliminarWorkers extends Command
{
    protected $signature = 'eliminar-workers {cola}';
    protected $description = 'Elimina los workers y el archivo de configuración de Supervisor para la cola indicada';

    public function handle()
    {
        $cola = trim($this->argument('cola'));

        if ($cola === '') {
            $this->error('Debe indicar el nombre de la cola.');
            return Command::FAILURE;
        }

        $rutaConfDir = '/var/www/html/mi_config';
        $program = "cola-{$cola}";
        $archivoConf = "{$rutaConfDir}/{$program}.conf";

        try {
            $this->info("Deteniendo workers de la cola '{$cola}'...");
            exec("supervisorctl stop {$program}:*", $outStop, $codeStop);

            if ($codeStop !== 0) {
                $this->warn("No se pudo (o no era necesario) detener {$program}. Puede que no existiera.");
            }

            if (file_exists($archivoConf)) {
                if (@unlink($archivoConf)) {
                    $this->info("Archivo eliminado: {$archivoConf}");
                } else {
                    $this->warn("No se pudo eliminar el archivo: {$archivoConf}");
                }
            } else {
                $this->warn("No existe el archivo de configuración: {$archivoConf}");
            }

            exec("supervisorctl reread");
            exec("supervisorctl update");

            $this->info("Workers y configuración de '{$cola}' eliminados.");
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('Error al eliminar workers', ['exception' => $e]);
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}