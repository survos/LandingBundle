<?php

namespace Survos\LandingBundle\Controller;

use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    private $landingService;
    private $kernel;

    public function __construct(LandingService $landingService, KernelInterface $kernel)
    {
        $this->landingService = $landingService;
        $this->kernel = $kernel;
    }

    /**
     * @Route("/", name="survos_landing")
     */
    public function landing(Request $request)
    {
        return $this->render("@SurvosLanding/landing.html.twig", [
        ]);
    }

    /**
     * @Route("/credits", name="survos_landing_credits")
     */
    public function credits(Request $request)
    {
        $bundles = $this->kernel->getBundles();
        return $this->render("@SurvosLanding/credits.html.twig", [
            'bundles' => $bundles
        ]);
    }

}
