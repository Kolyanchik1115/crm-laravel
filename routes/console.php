<?php

declare(strict_types=1);

foreach (glob(base_path('modules/*/src/Infrastructure/Console/schedule.php')) as $scheduleFile) {
    require_once $scheduleFile;
}
