<?php
namespace SlimApi\Mvc\Generator;

use SlimApi\Generator\GeneratorInterface;

class ScaffoldGenerator implements GeneratorInterface
{
    /**
     *
     * @param GeneratorInterface $controllerGenerator
     * @param GeneratorInterface $modelGenerator
     *
     * @return
     */
    public function __construct(GeneratorInterface $controllerGenerator, GeneratorInterface $modelGenerator)
    {
        $this->controllerGenerator = $controllerGenerator;
        $this->modelGenerator      = $modelGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($name, $fields)
    {
        // fields are ignored in scaffolding, everything's included but models still have fields
        return ($this->modelGenerator->validate($name, $fields) && $this->controllerGenerator->validate($name, []));
    }

    /**
     * {@inheritdoc}
     */
    public function process($name, $fields, $options)
    {
        $this->modelGenerator->process($name, $fields, $options);
        $this->controllerGenerator->process($name, [], $options);
    }
}
