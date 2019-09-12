<?php

namespace Survos\LandingBundle\Controller;

use App\Entity\ApiToken;
use App\Entity\User;
use App\Form\ForgotPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LandingController extends AbstractController
{
    private $landingService;
    private $entityManager;

    public function __construct(LandingService $landingService, EntityManagerInterface $entityManager)
    {
        $this->landingService = $landingService;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function landing(Request $request)
    {
        return $this->render("@SurvosLanding/landing.html.twig", [
        ]);
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile(Request $request)
    {
        return $this->render("@SurvosLanding/profile.html.twig", [
            'user' => $this->getUser()
        ]);
    }

    /**
     * @Route("/typography", name="app_typography")
     */
    public function typography(Request $request)
    {
        return $this->render("@SurvosLanding/typography.html.twig", [
            'user' => $this->getUser()
        ]);
    }


    /**
     * @Route("/impersonate", name="redirect_to_impersonate")
     */
    public function impersonate(Request $request)
    {
        $id = $request->get('id');
        $user = $this->entityManager->find(User::class, $id);

        $redirectUrl =$this->generateUrl('app_homepage', ['_switch_user' => $user->getEmail() ]);
        return new RedirectResponse($redirectUrl);
    }

    /**
     * @Route("/credits", name="survos_landing_credits")
     */
    public function credits(Request $request)
    {
        // bad practice to inject the kernel.  Maybe read composer.json and composer.lock
        $json = file_get_contents('../composer.json');

        return $this->render("@SurvosLanding/credits.html.twig", [
            'composerData' => json_decode($json, true),
        ]);
    }

    /**
     * @Route("/one-time-login-request", name="app_one_time_login_request")
     */
    public function oneTimeLoginRequest(Request $request)
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $email = $form->get('email')->getData();
            $oneTimeLogin = new ApiToken($email);
            $this->entityManager->persist($oneTimeLogin);
            $this->entityManager->flush();

            $this->addFlash('notice', "Email sent?");

            return $this->redirectToRoute('app_one_time_login_request');
        }

        return $this->render("@SurvosLanding/forgot_password.html.twig", [
            'form' => $form->createView()
        ]);


    }

}
