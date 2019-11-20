# landing-bundle

A moderately-opinionated bundle that provides a quick way to get up and running with Symfony, using best practices.  

Besides the Symfony web skeleton, this bundle also requires:

* Javascript: jquery, bootstrap
* Symfony: KnpMenu, WebpackEncore

and recommends:

* Javascript: fontawesome
* Symfony: EasyAdminBundle

### Goals

This bundle was created originally to isolate issues with other bundles and to get data on a website as quickly and painlessly as possible.  


### Requirements

* composer
* PHP 7.1+
* yarn
* Symfony Server (or another web server, like nginx)

### Using the bundle

The bundle assumes you've created your project from the base website skeleton


    PROJECT_DIR=fosuser && symfony new --full $PROJECT_DIR && cd $PROJECT_DIR

    PROJECT_DIR=test6 &&  symfony new --version=4.4.x-dev --full $PROJECT_DIR  && cd $PROJECT_DIR && symfony proxy:domain:attach $PROJECT_DIR.survos.com
    PROJECT_DIR=my-50 &&  symfony new --version=5.0.x-dev --full $PROJECT_DIR  && cd $PROJECT_DIR
    
    # composer config extra.symfony.allow-contrib true

    # interaction is required for the next commands, so if you're cutting and pasting, stop here!
    
    # use the defaults (App\Entity\User)
    bin/console make:user 

    # Create LoginFormAuthenticator
    bin/console make:auth
    
    # Optional, since SurvosBaseBundle has this already, formatted for mobile
    bin/console make:registration-form
    
    # Now install the Landing (SurvosBase?) bundle
    
    composer config repositories.survoslanding '{"type": "path", "url": "../Survos/LandingBundle"}'

    # at the moment, LandingBundle has a dependency on SwiftMailer
    composer req mail
    composer req symfony/mailer
    composer req survos/landing-bundle:"*@dev"

    # composer req survos/landing-bundle

    # creates survos_landing.yaml (a recipe would be nicer!)    
    bin/console survos:init

    # introspection, creates menus, looks for entities, easyadmin, etc.
    bin/console survos:configure
     
    # symfony run -d yarn encore dev --watch

### Integrating Facebook and other OAuth

Go to https://github.com/knpuniversity/oauth2-client-bundle#step-1-download-the-client-library

e.g. 

    composer require league/oauth2-facebook

The create an app and enable login: https://developers.facebook.com/apps/

Need a config script that asks for the ID and sets it in .env.local (or Heroku, etc.)
    
https://developers.facebook.com/apps/558324821626788/settings/basic/

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

    symfony server:start --no-tls
    
When finished, the application will have a basic landing page with top navigation, optionally including login/registration pages.  Logged in users with ROLE_ADMIN will also (optionally) have links to easyadmin and api-platform.  

### Customizing the bundle

### Deploy to heroku



    composer config --unset repositories.survoslanding && composer update
    git commit -m "unset survoslanding" . && git push heroku master

https://devcenter.heroku.com/articles/deploying-symfony4
bin/console survos:setup-heroku



   
    

