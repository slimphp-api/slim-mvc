<?php
namespace SlimMvc\Generator;

use SlimApi\Model\ModelInterface;
use SlimApi\Migration\MigrationInterface;
use SlimApi\Interfaces\GeneratorServiceInterface;
use SlimApi\Generator\GeneratorInterface;

class ModelGenerator implements GeneratorInterface
{
    /**
     *
     * @param ModelInterface $modelService
     * @param MigrationInterface $migrationService
     * @param GeneratorServiceInterface $dependencyService
     *
     * @return
     */
    public function __construct(ModelInterface $modelService, MigrationInterface $migrationService, GeneratorServiceInterface $dependencyService)
    {
        $this->modelService      = $modelService;
        $this->migrationService  = $migrationService;
        $this->dependencyService = $dependencyService;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($name, $fields)
    {
        $name = ucfirst($name);
        if ($this->modelExists($name)) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function process($name, $fields)
    {
        $name = ucfirst($name);
        $this->processCreateMigration($name, $fields);
        $this->processCreateModel($name, $fields);
        $this->processCreateDI($name);
    }

    /**
     * Processes the fields to create the migration
     *
     * @param string $name
     * @param array $fields colon seperated field definitions
     *
     * @return void
     */
    private function processCreateMigration($name, $fields)
    {
        $this->migrationService->processCommand('create', $name);

        // name:type:limit:null:unique
        foreach ($fields as $fieldDefinition) {
            $fieldDefinition = explode(':', $fieldDefinition);
            $fieldDefinition = array_map(function($value) {
                return (strlen($value) === 0 ? NULL : $value);
            }, $fieldDefinition);
            $this->migrationService->processCommand('addColumn', ...$fieldDefinition);
        }

        $this->migrationService->processCommand('finalise');
        $this->migrationService->create($name);
    }

    /**
     * Process the fields to create the model!
     *
     * @param mixed $name
     * @param array $fields colon seperated field definitions
     *
     * @return void
     */
    private function processCreateModel($name, $fields)
    {
        // name:type:limit:null:unique
        foreach ($fields as $fieldDefinition) {
            $fieldDefinition = explode(':', $fieldDefinition);
            $this->modelService->processCommand('addColumn', ...$fieldDefinition);
        }

        $this->modelService->create($name);
    }

    /**
     * Create any dependency injection entries
     *
     * @param string $name
     *
     * @return
     */
    private function processCreateDI($name)
    {
        $this->dependencyService->processCommand('injectModel', $name);
        $this->dependencyService->create($name);
    }

    /**
     * Checks if the model class exists
     *
     * @param string $name
     *
     * @return bool
     */
    private function modelExists($name)
    {
        return is_file($this->modelService->targetLocation($name));
    }
}
