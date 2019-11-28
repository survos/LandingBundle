<?php

namespace Survos\LandingBundle\Entity;

abstract class SurvosBaseEntity
{
    abstract function getUniqueIdentifiers();

    public function getRP(?Array $addlParams=[]): array
    {
        return array_merge($this->getUniqueIdentifiers(), $addlParams);
    }

}
