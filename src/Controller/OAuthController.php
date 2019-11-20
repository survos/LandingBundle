<?php

namespace Survos\LandingBundle\Controller;

use App\Entity\User;
use App\Security\AppEmailAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class OAuthController extends AbstractController
{

    /** @var ClientRegistry  */
    private $clientRegistry;

    public function __construct(LandingService $landingService, EntityManagerInterface $entityManager, \Swift_Mailer $mailer, UserProviderInterface $userProvider)
    {
        $this->landingService = $landingService;
        $this->entityManager = $entityManager;

        $this->mailer = $mailer;
        $this->userProvider = $userProvider;

        $this->clientRegistry = $this->landingService->getClientRegistry();

    }

    /**
     * @Route("/oauth", name="oauth")
     */
    public function index(Request $request)
    {

        $oauthClients = $this->landingService->getOauthClients();

        $clientRegistry = $this->clientRegistry;

        $refresh = $request->get('refresh', false);

        $clients = array_map(function (string $clientKey ) use ($clientRegistry, $refresh) {
            $client = $clientRegistry->getClient($clientKey);

            dump($client->redirect());

            // makes a call to the API to get the basic information, not a login\
            if ($refresh) {
                $apiInfo = $client->getOAuth2Provider()->getResourceOwnerDetailsUrl($client->getAccessToken());
                dump($apiInfo);
            }

            $redirect = $client->redirect([], []);
            $provider = $client->getOAuth2Provider();

            //
            // dd($provider, $provider->getGuarded(), $provider->getHeaders(), $provider->getHttpClient());
            return [
                'key' => $clientKey,
                'type' => get_class($provider),
                'redirect' => $redirect
            ];
        }, $clientRegistry->getEnabledClientKeys());

//        dd($clients);

        return $this->render('@SurvosLanding/oauth/oauth_clients.html.twig', [
            'clients' => $oauthClients,
            'clientKeys' =>  $clientRegistry->getEnabledClientKeys(),
            'clientRegistry' => $clientRegistry
        ]);
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/social_login/{clientKey}", name="connect_start")
     */
    public function connectAction(string $clientKey)
    {
        // scopes are client-specific, need to put them in survos_oauth or landing or (ideally) in knp's config
        $scopes =
            [
                'github' => [
                    "user:email", "read:user",
                ],
                'facebook' => []
            ];
        ;
        // will redirect to an external OAuth server
        $redirect =  $this->clientRegistry
            ->getClient($clientKey) // key used in config/packages/knpu_oauth2_client.yaml
            ->redirect($scopes[$clientKey], [])
            ;
        // dd($redirect);
        return $redirect;
    }

    /**
     * After going to Github, you're redirected back here
     * because this is the "redirect_route" you configured
     * in config/packages/knpu_oauth2_client.yaml
     *
     * @Route("/connect/check-guard", name="connect_github_check_with_guard")
     */
    public function connectCheckAction(Request $request, UserProviderInterface $userProvider)
    {
        // ** if you want to *authenticate* the user, then
        // leave this method blank and create a Guard authenticator
        // (read below)

        // leave it blank, per the instructions, and handle the redirect in the Guard

        // if it comes back from the guard to here,
        $user = $this->getUser();
        if ($user->getId()) {
            $targetUrl = $this->router->generate('app_homepage', ['login' => 'success']);
        } else {
            $targetUrl = $this->router->generate('app_register', ['email' => $user->getEmail()]);
        }
        return new RedirectResponse($targetUrl);

    }


}
