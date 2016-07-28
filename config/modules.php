<?php
/**
 * Register application modules
 */

$application->registerModules(array(
    // Frontend for framework
    'frontend' => array(
        'className' => 'Modules\Modules\Frontend\Module',
        'path' => __DIR__ . '/../apps/frontend/Module.php'
    ),

    // Data Engine 
    'dataengine' => array(
        'className' => 'AMPortal\DataEngine',
        'path'      => __DIR__ . '/../apps/dataengine/Module.php',
    ),
));
