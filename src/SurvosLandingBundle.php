<?php
namespace Survos\LandingBundle;

use Survos\LandingBundle\DependencyInjection\Compiler\SurvosLandingCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
class SurvosLandingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SurvosLandingCompilerPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
