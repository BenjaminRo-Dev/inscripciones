sail artisan queue:listen
docker exec -it laravel-inscripciones php artisan queue:listen
docker exec -it laravel-inscripciones php artisan test --filter=CupoTest

**Contenedor**
Ingresar al contenedor:
    docker exec -it laravel-inscripciones bash

**Supervisor**
Detener todas las instncias del proceso supervisord:
    pkill supervisord

Iniciar el proceso supervisord utilizando el archivo de configuracion /var/www/ht...:
    docker exec -it laravel-inscripciones supervisord -c /var/www/html/mi_config/supervisord.conf
    
    Ingresar como root:
    docker exec -u root -it laravel-inscripciones supervisord -c /var/www/html/mi_config/supervisord.conf

    Dentro del contenedor o de la instancia:
    supervisord -c /var/www/html/mi_config/supervisord.conf

Consultar el estado actual de los procesos que esta supervisando supervisord:
    supervisorctl -c /var/www/html/mi_config/supervisord.conf status

Detener manual una cola:
    supervisorctl -c /var/www/html/docker/supervisord.conf stop laravel-cola-default:*


Ejecutar un comando dentro del contenedor:
    docker exec -it laravel-inscripciones php artisan


**DOCKER**
Volver a armar una imagen:
    docker compose build laravel.inscripciones
    docker compose up -d
