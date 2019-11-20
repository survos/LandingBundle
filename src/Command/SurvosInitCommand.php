<?php

namespace Survos\LandingBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class SurvosInitCommand extends Command
{
    protected static $defaultName = 'survos:init';

    private $projectDir;
    private $kernel;
    private $em;
    private $twig;

    /** @var SymfonyStyle */
    private $io;

    CONST recommendedBundles = [
        'EasyAdminBundle',
        'SurvosWorkflowBundle',
        'UserBundle'
    ];

    CONST requiredJsLibraries = [
        'jquery',
        'bootstrap',
        'fontawesome',
        'popper.js'
    ];

    public function __construct(KernelInterface $kernel, EntityManagerInterface $em, \Twig\Environment $twig, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->em = $em;
        $this->projectDir = $kernel->getProjectDir();
        $this->twig = $twig;
    }
    protected function configure()
    {

        $this
            ->setDescription('Basic environment: landing page, yarn install, sqlite in .env.local, ')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    private function getAppCode() {
        // app code is the directory
        $app_code = basename($this->kernel->getProjectDir());
       return $app_code;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $io = new SymfonyStyle($input, $output);

        // $this->handleFavicon($io);
        // https://favicon.io/favicon-generator/?t=Fa&ff=Lancelot&fs=99&fc=%23FFFFFF&b=rounded&bc=%23B4B

        $this->createTranslations($io);
        $this->checkYarn($io);
        $this->setupDatabase($io);
        $this->updateBase($io);

        // perhaps install required yarn modules here?  Then in setup have the optional ones?

        // configure the route
        if ($prefix = $io->ask("Landing Route Prefix", '/')) {
            $fn = '/config/routes/survos_landing.yaml';
            $config = [
                'survos_landing_bundle' => [
                    'resource' => '@SurvosLandingBundle/Controller/LandingController.php',
                    'prefix' => $prefix,
                    'type' => 'annotation'
                ],
                'survos_landing_bundle_oauth' => [
                    'resource' => '@SurvosLandingBundle/Controller/OAuthController.php',
                    'prefix' => $prefix,
                    'type' => 'annotation'
                ],

            ];
            file_put_contents($output = $this->projectDir . $fn, Yaml::dump($config));
            $io->comment($fn . " written.");
        }

        $io->success("Run xterm -e \"yarn run encore dev-server\" & install more bundles, then run bin/console survos:setup");
        return 0;
    }

    private function updateBase(SymfonyStyle $io) {
        $fn = '/templates/base.html.twig';
        if ($io->confirm("Replace $fn?")) {
            $this->writeFile($fn, '{% extends "@SurvosLanding/base.html.twig" %}');
        }
    }

    private function createTranslations(SymfonyStyle $io) {
        $fn = '/translations/messages.en.yaml'; // @todo: get current default language code
        if ($io->confirm("Replace $fn?")) {
            $appCode = $this->getAppCode();
            $t = [
                'home' => [
                    'intro' => "Intro to $appCode",
                    'title' => "$appCode Title",
                    'description' => "Edit <code>$fn</code> and change the messages to reflect what $appCode is all about! You <b>CAN</b> use HTML!"
                ]
            ];
            $this->writeFile($fn, Yaml::dump($t, 5));
        }
    }


    private function checkYarn(SymfonyStyle $io)
    {
        if (!file_exists($this->projectDir . '/yarn.lock')) {
            $io->warning("Installing base yarn libraries");
            echo exec('yarn install');
        }
    }

    private function setupDatabase(SymfonyStyle $io) {
        if ($io->confirm('Use sqlite database in .env.local', true)) {
            if (!file_exists($fn = $this->projectDir . '/.env.local')) {
                file_put_contents($fn, "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db");
            }
        }
    }

    private function writeFile($fn, $contents) {
        file_put_contents($output = $this->projectDir . $fn, $contents);
        $this->io->success($fn . " written.");
    }

}
