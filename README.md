# landing-bundle

A moderately-opinionated bundle that provides a quick way to get up and running with Symfony, using best practices.  

Besides the Symfony web skeleton, this bundle also requires:

* Javascript: jquery, bootstrap
* Symfony: KnpMenu, WebpackEncore

and recommends:

* Javascript: fontawesome
* Symfony: MsgPhp/UserBundle, EasyAdminBundle

### Goals

This bundle was created originally to isolate issues with other bundles and to get data on a website as quickly and painlessly as possible.  


### Requirements

* composer
* PHP 7.1+
* yarn
* Symfony Server (or another web server, like nginx)

### Using the bundle

The bundle assumes you've created your project from the base website skeleton

    PROJECT_DIR=my-app4 && symfony new --full $PROJECT_DIR && cd $PROJECT_DIR
    composer config extra.symfony.allow-contrib true
    
    composer req maker --dev

    # interaction is required for the next commands, so if you're cutting and pasting, stop here!
    bin/console make:user 
    bin/console make:auth
    bin/console make:registration-form
    
    # 
    
    composer config repositories.survoslanding '{"type": "path", "url": "../Survos/LandingBundle"}'
    composer req survos/landing-bundle:"*@dev"

    # composer req survos/landing-bundle
    
    bin/console survos:init

    
    

### Integrating Facebook

    
    symfony server:start 

### Install and Configure UserBundle (optional)

See [docs/recommended_bundles]


#### If developing LandingBundle

    composer config repositories.survoslanding '{"type": "path", "url": "../Survos/LandingBundle"}'
    composer req survos/landing-bundle:"*@dev"

#### Normal installation

Install the bundle, then go through the setup to add and configure the tools.

    composer req survos/landing-bundle
    
    yarn install 
    xterm -e "yarn run encore dev-server" &
    
    bin/console survos:init

    bin/console survos:config --no-interaction
    bin/console doctrine:schema:update --force
    
#### Now install some bundles!
     
    See the details at [Recommended Bundles](docs/recommended-bundles.md)

If you chosen to integrate the userbundle, update the schema and add an admin    
    
    bin/console doctrine:schema:update --force
    bin/console msgphp:make:user

    symfony server:start 
    
When finished, the application will have a basic landing page with top navigation, optionally including login/registration pages.  Logged in users with ROLE_ADMIN will also (optionally) have links to easyadmin and api-platform.  

### Customizing the bundle

### Deploy to heroku


   
    

