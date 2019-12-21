<?php

namespace Survos\LandingBundle\Entity;

abstract class SurvosBaseEntity
{
    abstract function getUniqueIdentifiers();

    public function getRP(?Array $addlParams=[]): array
    {
        return array_merge($this->getUniqueIdentifiers(), $addlParams);
    }

    public function __toString()
    {
        return join('-', array_values($this->getUniqueIdentifiers()));
    }

}
