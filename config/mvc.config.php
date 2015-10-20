<?php
$config = [];

$config['templateDir'] = function($container) {
    return realpath(__DIR__.'/../templates');
};

$config['services.skeleton.structure'] = function($container) {
    // I thought long and hard about how to declare dependencies
    // should there be one for each type? controllers, services, models?
    // in the end it's not going to be managed by the api generators,
    // so a generic starting place for people to begin with seemed sensible
    $module           = file_get_contents($container->get('templateDir').'/Module.txt');
    $apiConfig        = file_get_contents($container->get('templateDir').'/apiConfig.txt');
    $mvcConfig        = file_get_contents($container->get('templateDir').'/mvc.config.txt');
    $slimConfig       = file_get_contents($container->get('templateDir').'/slim.config.txt');
    $sfConfig         = file_get_contents($container->get('templateDir').'/service-factories.config.txt');
    $index            = file_get_contents($container->get('templateDir').'/index.txt');
    $middleware       = file_get_contents($container->get('templateDir').'/middleware.txt');
    $routes           = file_get_contents($container->get('templateDir').'/routes.txt');
    $bootstrap        = file_get_contents($container->get('templateDir').'/bootstrap.txt');
    $gitignore        = file_get_contents($container->get('templateDir').'/gitignore.txt');
    $composer         = file_get_contents($container->get('templateDir').'/composer.txt');
    $phpunitxml       = file_get_contents($container->get('templateDir').'/phpunitxml.txt');
    $phpunitbootstrap = file_get_contents($container->get('templateDir').'/phpunitbootstrap.txt');

    return [
        'config' => [
            'slim-api.config.php'          => $apiConfig,
            'mvc.config.php'               => $mvcConfig,
            'slim.config.php'              => $slimConfig,
            'service-factories.config.php' => $sfConfig,
        ],
        'migrations' => [
            '.gitkeep' => '',
        ],
        'public' => [
            'index.php' => $index
        ],
        'src' => [
            'Controller'       => [
                '.gitkeep' => '',
            ],
            'Model'            => [
                '.gitkeep' => '',
            ],
            'bootstrap.php'  => $bootstrap,
            'middleware.php' => $middleware,
            'Module.php'     => $module,
            'routes.php'     => $routes,
        ],
        'tests' => [
            'phpunit' => [
                'bootstrap.php' => $phpunitbootstrap
            ]
        ],
        '.gitignore'       => $gitignore,
        'composer.json'    => $composer,
        'phpunit.xml.dist' => $phpunitxml,
        'README.md'        => 'I\'m a readme, see me roar!'
    ];
};

$config['SlimApi\Mvc\Controller\ControllerInterface'] = function($container) {
    return 'SlimApi\Mvc\Controller\ControllerService';
};

$config['SlimApi\Mvc\Controller\ControllerInterface.populated'] = function($container) {
    $indexAction     = file_get_contents($container->get('templateDir').'/indexAction.txt');
    $getAction       = file_get_contents($container->get('templateDir').'/getAction.txt');
    $postAction      = file_get_contents($container->get('templateDir').'/postAction.txt');
    $putAction       = file_get_contents($container->get('templateDir').'/putAction.txt');
    $deleteAction    = file_get_contents($container->get('templateDir').'/deleteAction.txt');
    $controllerClass = file_get_contents($container->get('templateDir').'/ControllerClass.txt');
    $controllerCons  = file_get_contents($container->get('templateDir').'/ControllerConstructor.txt');
    $service         = $container->get('SlimApi\Mvc\Controller\ControllerInterface');
    return new $service($indexAction, $getAction, $postAction, $putAction, $deleteAction, $controllerClass, $controllerCons, $container->get('namespace'));
};

$config['SlimApi\Mvc\Controller\ControllerInterface.empty'] = function($container) {
    $indexAction     = file_get_contents($container->get('templateDir').'/emptyIndexAction.txt');
    $getAction       = file_get_contents($container->get('templateDir').'/emptyGetAction.txt');
    $postAction      = file_get_contents($container->get('templateDir').'/emptyPostAction.txt');
    $putAction       = file_get_contents($container->get('templateDir').'/emptyPutAction.txt');
    $deleteAction    = file_get_contents($container->get('templateDir').'/emptyDeleteAction.txt');
    $controllerClass = file_get_contents($container->get('templateDir').'/ControllerClass.txt');
    $service         = $container->get('SlimApi\Mvc\Controller\ControllerInterface');
    return new $service($indexAction, $getAction, $postAction, $putAction, $deleteAction, $controllerClass, '', $container->get('namespace'));
};

$config['SlimApi\Service\DependencyService'] = function($container) {
    $service = new SlimApi\Service\DependencyService('config/mvc.config.php', $container->get('namespace'));
    $service->add('injectController', file_get_contents($container->get('templateDir').'/ControllerDependency.txt'));
    $service->add('injectModel', file_get_contents($container->get('templateDir').'/ModelDependency.txt'));
    return $service;
};

$config['SlimApi\Mvc\Generator\ModelGenerator'] = function($container) {
    return new SlimApi\Mvc\Generator\ModelGenerator($container->get('SlimApi\Model\ModelInterface'), $container->get('SlimApi\Migration\MigrationInterface'), $container->get('SlimApi\Service\DependencyService'));
};

$config['SlimApi\Mvc\Generator\ControllerGenerator.empty'] = function($container) {
    return new SlimApi\Mvc\Generator\ControllerGenerator($container->get('SlimApi\Mvc\Controller\ControllerInterface.empty'), $container->get('SlimApi\Service\RouteService'), $container->get('SlimApi\Service\DependencyService'));
};

$config['SlimApi\Mvc\Generator\ControllerGenerator.populated'] = function($container) {
    return new SlimApi\Mvc\Generator\ControllerGenerator($container->get('SlimApi\Mvc\Controller\ControllerInterface.populated'), $container->get('SlimApi\Service\RouteService'), $container->get('SlimApi\Service\DependencyService'));
};

$config['SlimApi\Mvc\Generator\ScaffoldGenerator'] = function($container) {
    return new SlimApi\Mvc\Generator\ScaffoldGenerator($container->get('SlimApi\Mvc\Generator\ControllerGenerator.populated'), $container->get('SlimApi\Mvc\Generator\ModelGenerator'));
};

$config['SlimApi\Mvc\Init'] = function($container) {
    if ($container->has('namespace')) {
        $container['SlimApi\Factory\GeneratorFactory']->add('model', $container->get('SlimApi\Mvc\Generator\ModelGenerator'));
        $container['SlimApi\Factory\GeneratorFactory']->add('controller', $container->get('SlimApi\Mvc\Generator\ControllerGenerator.empty'));
        $container['SlimApi\Factory\GeneratorFactory']->add('scaffold', $container->get('SlimApi\Mvc\Generator\ScaffoldGenerator'));
    }
};

return $config;
