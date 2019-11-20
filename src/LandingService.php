<?php

namespace Survos\LandingBundle;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Yaml\Yaml;

class LandingService
{
    private $entityClasses;
    /**
     * @var ClientRegistry
     */
    private $clientRegistry;

    public function __construct(array $entityClasses, ClientRegistry $clientRegistry)
    {
        $this->entityClasses = $entityClasses;
        $this->clientRegistry = $clientRegistry;
    }

    public function getEntities()
    {
        return $this->entityClasses;
    }

    public function getOauthClientKeys(): array {
        return $this->clientRegistry->getEnabledClientKeys();
    }

    // hack to get client id
    private function accessProtected($obj, $prop) {
        $reflection = new \ReflectionClass($obj);
        $property = $reflection->getProperty($prop);
        $property->setAccessible(true);
        return $property->getValue($obj);
    }

    public function getOauthClients($all = false): array {
        $providers = $this->getOAuthProviders();

        // really need to get all TYPES (facebook, etc.), then group the clients under them, plus have the provider data.
        // eventually we'll want an admin client to display the related apps

        $keys = $this->clientRegistry->getEnabledClientKeys();


        return array_combine($keys, array_map(function ($key) use ($providers) {
            $client = $this->clientRegistry->getClient($key);
            $provider = $client->getOAuth2Provider();
            $clientId = $this->accessProtected($provider, 'clientId');
            // $extra = $this->accessProtected($provider, 'extrias');
            return [
                'key' => $key,
                'provider' => $providers[$key],
                'client' => $client,
                'appId' => $clientId
            ];
        }, $keys) );
    }

    public function getClientRegistry(): ClientRegistry
    {
        return $this->clientRegistry;
    }

    protected static function getPath(): string
    {
        return __DIR__.'/../Resources/data/providers.yaml';
    }

    public function getOAuthProviders(): array
    {
        return Yaml::parseFile(self::getPath())['providers'];
        // $addlData = $this->getOAuthProviders();
    }



}

