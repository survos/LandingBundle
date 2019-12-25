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

    public function XXgetPath(): string
    {
        return \dirname(__DIR__); // use the newer bundle structure, /templates instead of /Resources/views
    }
}
