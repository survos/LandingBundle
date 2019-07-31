<?php

namespace Survos\LandingBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
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

class SurvosSetupCommand extends ContainerAwareCommand
{
    use ContainerAwareTrait;
    protected static $defaultName = 'survos:setup';

    private $projectDir;
    private $kernel;
    private $em;

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

    public function __construct(KernelInterface $kernel, Registry $registry, string $name = null)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->em = $registry->getEntityManager();
        $this->projectDir = $kernel->getProjectDir();
    }

    public function setEntityManager(EntityManagerInterface $entityManager) {
        $this->em = $entityManager;
    }

    protected function configure()
    {

        $this
            ->setDescription('Setup libraries and basic landing page')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $this->checkYarn($io);

        if ($io->confirm('Remove MySQL-specific DBAL configuration?', true)) {
            $config = Yaml::parseFile($configFile = $this->projectDir . '/config/packages/doctrine.yaml');

            $replaceDbal = [
                'url' => $config['doctrine']['dbal']['url']
            ];

            $config['doctrine']['dbal'] = $replaceDbal;
            file_put_contents($configFile, Yaml::dump($config));
        }


        $this->checkBundles($io);
        $this->checkEntities($io);
        $this->updateBase($io);

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


        if ($prefix = $io->ask("Application Menu Class", 'App/Menu/AppMenuBuilder')) {
            $fn = '/src/Menu/AppMenuBuilder.php';
            // use twig? Php?
            $io->comment($fn . " NOT written.");
        }

        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('Start your server and go to ' . $prefix);
    }

    private function checkEntities(SymfonyStyle $io) {
        $entities = array();
        $em = $this->em;
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entities[] = $m->getName();
        }
        dump($entities);
    }

    private function updateBase(SymfonyStyle $io) {
        $fn = '/templates/base.html.twig';
        if ($io->confirm("Replace $fn with one that load js and css assets?")) {
            file_put_contents($output = $this->projectDir . $fn, '{% extends "@SurvosLanding/base.html.twig" %}');
            $io->comment($output . " written.");
        }
    }

    private function checkYarn(SymfonyStyle $io)
    {
        $json = exec(sprintf('yarn list --pattern "(%s)" --json', join('|', self::requiredJsLibraries)) );

            $yarnModules = json_decode($json, true);

            $modules = array_map(function ($tree) {
                if (is_string($tree)) {
                    return $tree;
                }
                [$name, $version] = explode('@', $tree['name']);
                return $name;
            }, $yarnModules['data']['trees'] );

            // sort($modules); dump($modules); die();
        try {
        } catch (\Exception $e) {
            $io->error("Yarn failed -- is it installed? " . $e->getMessage());
        }

        $missing = array_diff(self::requiredJsLibraries, $modules);

        if ($missing) {
            $io->error("Missing " . join(',', $missing));
            $command = sprintf("yarn add %s --dev", join(' ', $missing));
            if ($io->confirm("Install them now? with $command? ", true)) {
                echo exec($command) . "\n";
            } else {
                die("Cannot continue without yarn modules");
            }
        }

        // echo exec('yarn run encore dev');
        /* better: */
        $process = new Process(['yarn', 'run', 'encore', 'dev']);
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();


    }
    private function checkBundles(SymfonyStyle $io)
    {
        $bundles = $this->kernel->getBundles();

        foreach (self::recommendedBundles as $bundleName) {
            if (empty($bundles[$bundleName])) {
                $io->warning($bundleName . ' is recommended, install it using composer req ' . $bundleName);
            }
        }

        foreach ($bundles as $bundleName) {

        }

    }
}
