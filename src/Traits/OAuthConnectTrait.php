<?php

namespace Survos\LandingBundle\Traits;

use App\Entity\User;
use App\Security\AppEmailAuthenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\HttpFoundation\Request;

trait OAuthConnectTrait

{
    /**
     * This is where the user is redirected to after logging into the OAuth server,
     * see the "redirect_route" in config/packages/knpu_oauth2_client.yaml
     *
     * @\Symfony\Component\Routing\Annotation\Route("/connect/controller/{clientKey}", name="connect_check_with_controller")
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


        /** @var \KnpU\OAuth2ClientBundle\Client\Provider\GithubClient $client */
        dump($clientKey);
        $client = $clientRegistry->getClient($clientKey);
        dump($client);

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