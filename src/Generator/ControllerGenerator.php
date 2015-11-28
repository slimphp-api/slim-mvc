<?php
namespace SlimApi\Mvc\Generator;

use SlimApi\Mvc\Controller\ControllerInterface;
use SlimApi\Interfaces\GeneratorServiceInterface;
use SlimApi\Generator\GeneratorInterface;

class ControllerGenerator implements GeneratorInterface
{
    private $validActions = ['index', 'get', 'post', 'put', 'delete'];

    /**
     *
     * @param ControllerInterface $controllerService
     * @param GeneratorServiceInterface $routeService
     * @param GeneratorServiceInterface $dependencyService
     *
     * @return void
     */
    public function __construct(ControllerInterface $controllerService, GeneratorServiceInterface $routeService, GeneratorServiceInterface $dependencyService)
    {
        $this->controllerService = $controllerService;
        $this->routeService      = $routeService;
        $this->dependencyService = $dependencyService;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($name, $fields)
    {
        $name = ucfirst($name);
        if ($this->controllerExists($name)) {
            return false;
        }

        foreach ($fields as $possibleAction) {
            if (!in_array($possibleAction, $this->validActions)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process($name, $fields, $options)
    {
        $name = ucfirst($name);
        if (0 === count($fields)) {
            $fields = $this->validActions;
        }

        foreach ($options as $optionName => $optionValue) {
            if (!in_array($optionName, ['versions'])) {
                unset($options[$optionName]);
            }
        }

        foreach ($fields as $action) {
            $this->controllerService->processCommand('addAction', $action);
        }

        $this->routeService->processCommand('addRoute', '/'.$name, ...$fields);
        $this->dependencyService->processCommand('injectController', $name);

        $this->controllerService->create($name);
        $this->routeService->create($name, $options);
        $this->dependencyService->create($name, $options);
    }

    /**
     * Checks if controller exists!
     *
     * @param string $name
     *
     * @return bool
     */
    private function controllerExists($name)
    {
        return is_file($this->controllerService->targetLocation($name));
    }
}
