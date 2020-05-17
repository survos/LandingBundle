# landing-bundle

@todo:

Add new Bundle directory convention consistent with standard skeletons:

    └── MyBundle/
        ├── config/
        ├── public/
        ├── src/
        │   └── MyBundle.php
        ├── templates/
        └── translations/
    
        class MyBundle extends Bundle
        {
            public function getPath(): string
            {
                return \dirname(__DIR__);
            }
        }


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
* Symfony CLI (for running a local server, creating project, etc.)

### Create github Project

* Create a NEW repository 

    https://github.com/new

on github.com with no files (no README or license), clone it to some directory and go there.

     REPO=xml-serializer-demo && git clone git@github.com:tacman/$REPO.git && cd $REPO 
     
* Create the Symfony Skeleton WITHOUT a git repo, then ADD the repo.
     
    mv .git .. && symfony new --full . --no-git  && mv ../.git .
    
Create the project on heroku, after logging in

    heroku create $REPO
     bin/console make:user User --is-entity --identity-property-name=email --with-password -n

    # composer config extra.symfony.allow-contrib true

    # interaction is required for the next commands, so if you're cutting and pasting, stop here!
    
    # use the defaults (App\Entity\User)

### Create LoginFormAuthenticator
```bash
bin/console make:auth

   1 # Login Form Authenticator
   AppAuthenticator
   <return> # SecurityController
   <return> # /logout
```
    
    # Optional, since SurvosBaseBundle has this already, formatted for mobile
    bin/console make:registration-form
    
    # Now install the Landing (SurvosBase?) bundle
    composer config minimum-stability dev
    composer req survos/landing-bundle
    
        composer config repositories.adminlte '{"type": "vcs", "url": "git@github.com:tacman/AdminLTEBundle.git"}'

        composer config repositories.blog '{"type": "vcs", "url": "git@github.com:survos/OdiseoBlogBundle.git"}'

    # local dev: create a symlink

    composer config repositories.survoslanding '{"type": "path", "url": "../Survos/LandingBundle"}'
    composer config repositories.geonames '{"type": "path", "url": "../Survos/geonames-bundle"}'
    composer config repositories.phpspreadsheet '{"type": "path", "url": "../Survos/phpspreadsheet-bundle"}'
    
    composer config repositories.multisearch '{"type": "vcs", "url": "git@github.com:tacman/PetkoparaMultiSearchBundle.git"}'
    
    composer config repositories.captcha '{"type": "vcs", "url": "git@github.com:cyrilverloop/symfony-captcha-bundle.git"}'

    composer config repositories.git-survoslanding '{"type": "vcs", "url": "https://github.com/survos/LandingBundle.git"}'

    composer config repositories.git-geonames '{"type": "vcs", "url": "https://github.com/survos/geonames-bundle.git"}'

    composer config repositories.flowdemo '{"type": "path", "url": "../Survos/../CraueFormFlowDemoBundle"}'

    
    composer config repositories.social_post_bundle '{"type": "path", "url": "../Survos/social-post-bundle"}'

    composer config repositories.social_post_bundle '{"type": "vcs", "url": "https://github.com/tacman/social-post-bundle"}'

    # this is needed because it creates MAILER_DSN, which isn't created otherwise
    # composer req mail
    composer req knplabs/knp-menu-bundle:"^3.0@dev"

    composer req survos/landing-bundle:"*@dev"
    phpstorm .env

OR

    composer req survos/landing-bundle

    # creates survos_landing.yaml (a recipe would be nicer!)    
    bin/console survos:init
    
    # edit .env and set MAILER_URL
    
# Ugh, still doesn't work, needs a landing menu    

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
    
    composer req "kevinpapst/adminlte-bundle"
    bin/console make:subscriber KnpMenuSubscriber "KevinPapst\AdminLTEBundle\Event\KnpMenuEvent"
    
    bin/console survos:init
    
    

    bin/console survos:config --no-interaction
    bin/console doctrine:schema:update --force
    
#### survos:init

First time setup, downloads jquery, bootstrap, etc.
Also _modifies_ some yaml files, and creates the first menu.  

```yaml
# config/packages/admin_lte.yaml
admin_lte:
    knp_menu:
        enable: true

    routes:
        adminlte_welcome: app_homepage
        adminlte_login: app_login
        adminlte_profile: app_profile
```

@todo: Generate this Controller and templates?

```yaml
# config/routes/survos_landing.yaml
survos_landing: {path: /, controller: 'Survos\LandingBundle\Controller\LandingController::landing'}
# app_homepage: {path: /, controller: 'Survos\LandingBundle\Controller\LandingController::landing'}
app_logo: {path: /logo, controller: 'Survos\LandingBundle\Controller\LandingController::logo'}
app_profile: {path: /profile, controller: 'Survos\LandingBundle\Controller\LandingController::profile'}
# profile: {path: /profile, controller: 'Survos\LandingBundle\Controller\LandingController::profile'}
# logout: {path: /logout, controller: 'Survos\LandingBundle\Controller\LandingController::logout'}
# required if app_profile is used, since you can change the password from the profile
app_change_password: {path: /change-password, controller: 'Survos\LandingBundle\Controller\LandingController::changePassword'}
```


{% extends '@AdminLTE/layout/default-layout.html.twig' %}
{% block page_content %}
{{ block('body') }}
{% endblock %}

{% block logo_mini %}<b>KPA</b>{% endblock %}
{% block logo_large '<b>KPA</b> Admin' %}

{% block page_title 'KPA Admin' %}
{% block page_subtitle 'Songs and Music!' %}

#### Now install some bundles!
     
    See the details at [Recommended Bundles](docs/recommended-bundles.md)

If you chosen to integrate the userbundle, update the schema and add an admin    
    
    bin/console doctrine:schema:update --force

    symfony server:start --no-tls
    
When finished, the application will have a basic landing page with top navigation, optionally including login/registration pages.  Logged in users with ROLE_ADMIN will also (optionally) have links to easyadmin and api-platform.  

### Api Platform

@todo: put this in a survos:setup command.

* Expose the API routes (for jsRoutingBundle), and 

```yaml
# config/routes/api_platform.yaml
api_platform:
    resource: .
    type: api_platform
    prefix: /api
    options:
        expose: true
```

Create resources.yaml to store the configuration
```yaml
# api/config/api_platform/resources.yaml
App\Entity\User: ~
App\Entity\Location:
  shortName: 'Location'                   # optional
  description: 'A place within a building where inventory item is physically located.' # optional
  attributes:                          # optional
    pagination_items_per_page: 30   # optional
    normalization_context:
      groups: ['jstree']
    denormalization_context:
      groups: ['jstree']
```

Add the resources.yaml directory to the mapping paths

```yaml
# config/packages/api_platform.yaml
api_platform:
    mapping:
        paths:
            - '%kernel.project_dir%/src/Entity'
            - '%kernel.project_dir%/config/api_platform' # yaml or xml directory configuration]
    patch_formats:
        json: ['application/merge-patch+json']
    swagger:
        versions: [3]
```

Configure the serializer (may need to create the directory)

```yaml
# config/serializer/serialization.yaml
App\Entity\User:
  attributes:
    id:
      groups: ['Default']
    email:
      groups: ['Default']

App\Entity\Song:
  attributes:
    title:
      groups: ['Default']
```

### Customizing the bundle

### Deploy to heroku

    heroku create $projectName
    
    echo "web:  vendor/bin/heroku-php-nginx -C heroku-nginx.conf  -F fpm_custom.conf public/" > Procfile

    heroku buildpacks:add heroku/nodejs
    heroku buildpacks:add --index 2 heroku/nodejs

    
    composer config --unset repositories.survoslanding && composer update
    git commit -m "unset survoslanding" . && git push heroku master

https://devcenter.heroku.com/articles/deploying-symfony4
bin/console survos:setup-heroku



   
    

