<?php

declare(strict_types=1);

foreach (glob(base_path('modules/*/src/Interfaces/Http/routes/api.php')) as $routeFile) {
    require_once $routeFile;
}
