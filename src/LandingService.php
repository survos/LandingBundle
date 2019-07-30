<?php

namespace Survos\LandingBundle;

class LandingService
{
    private $entityClasses;
    private $configManager;
    public function __construct(array $entityClasses)
    {
        $this->entityClasses = $entityClasses;
        // dump($entityClasses); die("Stopped");
    }

    public function getEntities()
    {
        // return $this->configManager->getBackendConfig();
        return $this->entityClasses;
    }

}

