<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CrearWorkers extends Command
{
    protected $signature = 'crear-workers {cola} {cant}';
    protected $description = 'Gestionar workers para las colas de RabbitMQ';

    public function handle()
    {
        try {
            $cola = $this->argument('cola');
            $cant = (int) $this->argument('cant');

            $this->info("Asignando {$cant} workers a la cola: {$cola}");

            $rutaConfDir = '/var/www/html/mi_config';
            if (!file_exists($rutaConfDir)) {
                mkdir($rutaConfDir, 0775, true);
            }

            $archivoConf = $rutaConfDir . "/cola-{$cola}.conf";

            // Crear o actualizar el archivo .conf de la cola
            $contenido = "[program:cola-{$cola}]\n"
                . "process_name=%(process_num)d\n"
                . "command=php /var/www/html/artisan queue:work rabbitmq --queue={$cola} --sleep=3 --tries=3\n"
                . "autostart=true\n"
                . "autorestart=true\n"
                . "numprocs={$cant}\n"
                . "redirect_stderr=true\n"
                . "stdout_logfile=/var/log/supervisor/cola-{$cola}.log\n";

            file_put_contents($archivoConf, $contenido);

            // Aplicar los cambios a Supervisor
            exec("supervisorctl reread");
            exec("supervisorctl update");

            // Reiniciar procesos de la cola para que use la nueva cantidad de workers
            exec("supervisorctl stop cola-{$cola}:*");
            if ($cant > 0) {
                exec("supervisorctl start cola-{$cola}:*");
            }

            $this->info("Cola '{$cola}' ahora tiene {$cant} workers activos.");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error("Error al gestionar los workers: " . $e->getMessage(), ['exception' => $e]);
            $this->error("Error al gestionar los workers: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}