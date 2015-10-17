<?php
namespace SlimMvc;

class Module
{
    /**
     * load the dependencies for the module.
     */
    public function loadDependencies()
    {
        $config = [];
        $files  = glob(dirname(__DIR__).'/config/*.config.php', GLOB_BRACE);
        foreach ($files as $file) {
            $config = array_merge($config, (require $file));
        }
        return $config;
    }
}
