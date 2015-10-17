<?php
namespace SlimMvcTest\Mock;

use SlimMvc\Generator\ModelGenerator;
use SlimMvc\Command\GenerateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ModelGeneratorMock extends ModelGenerator
{
    public function __construct()
    {
    }

    public function validate($name, $fields)
    {
        return true;
    }

    public function process($name, $fields)
    {
        return true;
    }
}
