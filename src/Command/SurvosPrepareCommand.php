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

class SurvosPrepareCommand extends Command
{
    protected static $defaultName = 'survos:prepare';

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

    public function __construct(KernelInterface $kernel, Registry $registry, \Twig\Environment $twig, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->em = $registry->getEntityManager();
        $this->projectDir = $kernel->getProjectDir();
        $this->twig = $twig;
    }
    protected function configure()
    {

        $this
            ->setDescription('Prepares environment after installing web-skeleton')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = $io = new SymfonyStyle($input, $output);

        // https://favicon.io/favicon-generator/?t=Fa&ff=Lancelot&fs=99&fc=%23FFFFFF&b=rounded&bc=%23B4B

        $this->checkYarn($io);
        $this->updateBase($io);
        $this->setupDatabase($io);

        // perhaps install required yarn modules here?  Then in setup have the optional ones?

        // configure the route
        if ($prefix = $io->ask("Landing Route Prefix", '/')) {
            $fn = '/config/routes/survos_landing.yaml';
            $config = [
                'survos_landing_bundle' => [
                    'resource' => '@SurvosLandingBundle/Controller/LandingController.php',
                    'prefix' => $prefix,
                    'type' => 'annotation'
                ]
            ];
            file_put_contents($output = $this->projectDir . $fn, Yaml::dump($config));
            $io->comment($fn . " written.");
        }

        $io->success("Run xterm -e \"yarn run encore dev-server\" & install more bundles, then run bin/console survos:setup");
    }

    private function updateBase(SymfonyStyle $io) {
        $fn = '/templates/base.html.twig';
        if ($io->confirm("Replace $fn?")) {
            $this->writeFile($fn, '{% extends "@SurvosLanding/base.html.twig" %}');
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
        if ($io->confirm('Remove MySQL-specific DBAL configuration?', true)) {
            $config = Yaml::parseFile($configFile = $this->projectDir . '/config/packages/doctrine.yaml');

            $replaceDbal = [
                'url' => $config['doctrine']['dbal']['url']
            ];

            $config['doctrine']['dbal'] = $replaceDbal;
            file_put_contents($configFile, Yaml::dump($config,1));
        }

        if ($io->confirm('Use sqlite database in .env.local', true)) {
            if (!file_exists($fn = $this->projectDir . '/.env.local')) {
                file_put_contents($fn, "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db");
            }
            $config = Yaml::parseFile($configFile = $this->projectDir . '/config/packages/doctrine.yaml');

            $replaceDbal = [
                'url' => $config['doctrine']['dbal']['url']
            ];

            $config['doctrine']['dbal'] = $replaceDbal;
            file_put_contents($configFile, Yaml::dump($config,1));
        }
    }

    private function writeFile($fn, $contents) {
        file_put_contents($output = $this->projectDir . $fn, $contents);
        $this->io->success($fn . " written.");
    }

}
