<?php

namespace Survos\LandingBundle\Traits;

trait FacebookTrait {

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getProjectId(): ?int {
        return $this->getProject() ? $this->getProject()->getId() : null;
    }

}
