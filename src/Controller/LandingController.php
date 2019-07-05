<?php

namespace Survos\LandingBundle\Controller;

use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class LandingController extends AbstractController
{
    private $landingService;

    public function __construct(LandingService $landingService)
    {
        $this->landingService = $landingService;
    }

    /**
     * @Route("/", name="survos_landing")
     */
    public function landing(Request $request)
    {
        return $this->render("@SurvosLanding/landing.html.twig", [
        ]);
    }

}
