<?php

namespace Survos\LandingBundle;

class LandingService
{
    private $entityClasses;
    public function __construct(array $entityClasses)
    {
        $this->entityClasses = $entityClasses;
    }

    public function getEntities()
    {
        return $this->entityClasses;
    }

}

