<?php

use App\Kernel;

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

// Establecer la zona horaria obligatoria de toda la aplicación
date_default_timezone_set('America/Argentina/Buenos_Aires');

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
