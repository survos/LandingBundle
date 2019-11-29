<?php

namespace Survos\LandingBundle\Controller;

use App\Entity\User;
use Survos\LandingBundle\Security\AppEmailAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\Github;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Twig\Environment;

class OAuthController extends AbstractController
{

    /** @var ClientRegistry  */
    private $clientRegistry;

    public function __construct(LandingService $landingService,
                                EntityManagerInterface $entityManager, UserProviderInterface $userProvider)
    {
        $this->landingService = $landingService;
        $this->entityManager = $entityManager;

        $this->userProvider = $userProvider;

        $this->clientRegistry = $this->landingService->getClientRegistry();

    }

    public function socialMediaButtons($style)
    {
        return $this->render('@SurvosLanding/_social_media_login_buttons.html.twig', [
            'clientKeys' =>  $this->clientRegistry->getEnabledClientKeys(),
            'clientRegistry' => $this->clientRegistry,
            'style' => $style
        ]);

    }

    /**
     * @Route("/provider/{providerKey}", name="oauth_provider")
     */
    public function providerDetail(Request $request, $providerKey)
    {
        $provider = $this->landingService->getCombinedOauthData()[$providerKey];

        return $this->render('@SurvosLanding/oauth/provider.html.twig', [
            'provider' => $provider
            ]);

    }

    /**
     * @Route("/providers", name="oauth_providers")
     */
    public function index(Request $request)
    {

        $oauthClients = $this->landingService->getOauthClients();
        $clientRegistry = $this->clientRegistry;

        $refresh = $request->get('refresh', false);

        // what we want is ALL the available clients, with their configuration if available.

        // could move the array_map into the service call
        $clients = $this->landingService->getCombinedOauthData();

        return $this->render('@SurvosLanding/oauth/providers.html.twig', [
            'clients' => $clients,
            /*
            'clientKeys' =>  $clientRegistry->getEnabledClientKeys(),
            'clientRegistry' => $clientRegistry
            */
        ]);
    }

    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/social_login/{clientKey}", name="oauth_connect_start")
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
            ->redirect($scopes[$clientKey] ?? [], [])
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

    /**
     * This is where the user is redirected to after logging into the OAuth server,
     * see the "redirect_route" in config/packages/knpu_oauth2_client.yaml
     *
     * @\Symfony\Component\Routing\Annotation\Route("/connect/controller/{clientKey}", name="oauth_connect_check")
     */
    public function connectCheckWithController(Request $request,
                                               ClientRegistry $clientRegistry,
                                               \Doctrine\ORM\EntityManagerInterface $em,
                                               GuardAuthenticatorHandler $guardHandler,
                                               AppEmailAuthenticator $authentication,
                                               UserProviderInterface $userProvider,
                                               $clientKey
    )
    {
        // return new RedirectResponse($this->generateUrl('app_homepage'));

        if ($error = $request->get('error')) {
            $this->addFlash('error', $error);
            $this->addFlash('error', $request->get('error_description'));
            return $this->redirectToRoute('app_login');
        }


        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GithubClient $client */

        $client = $clientRegistry->getClient($clientKey);

        // the exact class depends on which provider you're using
        /** @var \League\OAuth2\Client\Provider\GithubResourceOwner $user */
        $user = $client->fetchUser();

        // github users don't have an email, so we have to fetch it.
        $email = $user->getEmail();

        // now presumably we need to link this up.
        $token = $user->getId();

        // do something with all this new power!
        // e.g. $name = $user->getFirstName();

        // if we have it, just log them in.  If not, direct to register


        // it seems that loadUserByUsername redirects to logig
        // if ($user = $userProvider->loadUserByUsername($email)) {
        if ($user = $em->getRepository(User::class)->findOneBy(['email' => $email])) {
// after validating the user and saving them to the database
            // authenticate the user and use onAuthenticationSuccess on the authenticator
            if ($user->getId()) {
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,          // the User object you just created
                    $request,
                    $authentication, // authenticator whose onAuthenticationSuccess you want to use
                    'main'          // the name of your firewall in security.yaml
                );
            } else {
                return new RedirectResponse($this->generateUrl('app_register', ['email' => $email, 'githubId = ']));
            }


        } else {

            // redirect to register, with the email pre-filled

            // return new RedirectResponse($this->generateUrl('app_register'));
            return new RedirectResponse($this->generateUrl('app_register', ['email' => $email, 'githubId' => $token]));

        }


        try {
            // ...
        } catch (IdentityProviderException $e) {
            // something went wrong!
            // probably you should return the reason to the user
            echo $e->getResponseBody();
            dd($e,  $e->getMessage());
        }

        return new RedirectResponse($this->generateUrl('app_register', ['email' => $email, 'githubId = ']));
    }


}
