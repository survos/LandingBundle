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

class SurvosSetupCommand extends Command
{
    protected static $defaultName = 'survos:setup';

    private $projectDir;
    private $kernel;
    private $em;
    private $twig;

    /** @var SymfonyStyle */
    private $io;

    CONST recommendedBundles = [
        'EasyAdminBundle' => ['repo' => 'admin'],
        'SurvosWorkflowBundle' => ['repo' => 'survos/workflow-bundle'],
        'MsgPhpUserBundle' => ['repo' => 'msgphp/user-bundle']
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
        $this->io = $io = new SymfonyStyle($input, $output);

        $this->checkBundles($io);
        $this->updateAssets($io);
        $this->checkYarn($io);

        // $this->checkEntities($io);
        $this->createConfig($io);

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

        $io->success('Start your server and go to ' . $prefix);
    }

    private function checkYarn(SymfonyStyle $io)
    {
        if (!file_exists($this->projectDir . '/yarn.lock')) {
            $io->error("run yarn install or bin/console survos:prepare first");
            die();
        }

        try {
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

    private function createConfig(SymfonyStyle $io) {

        $yaml = <<< END
services:
  survos.landing_menu_builder:
    class: Survos\LandingBundle\Menu\LandingMenuBuilder
    arguments:
      - "@knp_menu.factory"
      - "@security.authorization_checker"
    tags:
      #      - { name: knp_menu.menu_builder, method: createMainMenu, alias: landing_menu } # The alias is what is used to retrieve the menu
      - { name: knp_menu.menu_builder, method: createTestMenu, alias: test_menu }
      - { name: knp_menu.menu_builder, method: createTestMenu, alias: landing_menu }
      - { name: knp_menu.menu_builder, method: createAuthMenu, alias: auth_menu }

  app.menu_builder:
    class: App\Menu\MenuBuilder
    arguments:
      - "@knp_menu.factory"
      - "@security.authorization_checker"
    tags:
      - { name: knp_menu.menu_builder, method: createMainMenu, alias: landing_menu }
END;

        if ($prefix = $io->ask("Application Menu Class", 'App/Menu/MenuBuilder')) {
            $dir = $this->projectDir . '/src/Menu';
            $fn = '/src/Menu/MenuBuilder.php';
            if (!is_dir($dir)) {
                mkdir($dir);
            }
            $php = $this->twig->render("@SurvosLanding/MenuBuilder.php.twig", []);

            // $yaml =  Yaml::dump($config);
            file_put_contents($output = $this->projectDir . $fn, $php);
            $io->comment($fn . " written.");


            // use twig? Php?
            $fn = '/config/packages/survos_landing.yaml';
            file_put_contents($output = $this->projectDir . $fn, $yaml);
            $io->comment($fn . "  written.");
        }
    }

    private function checkEntities(SymfonyStyle $io) {
        $entities = array();
        $em = $this->em;
        $meta = $em->getMetadataFactory()->getAllMetadata();
        foreach ($meta as $m) {
            $entities[] = $m->getName();
        }

        // if there are entities and easyadmin, create the easyadmin.yaml file??
        dump($entities);
    }

    private function updateAssets(SymfonyStyle $io) {
        $fn = '/templates/base.html.twig';
        if ($io->confirm("Replace app assets (js and css)?")) {
            // @todo: specific to yarn packages
            try {
                $this->writeFile('/assets/js/app.js', $this->twig->render("@SurvosLanding/app.js.twig", []) );
                $this->writeFile('/assets/css/app.css', $this->twig->render("@SurvosLanding/app.css.twig", []) );
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }
    }

    private function checkBundles(SymfonyStyle $io)
    {
        $bundles = $this->kernel->getBundles();

        foreach (self::recommendedBundles as $bundleName=>$info) {
            if (empty($bundles[$bundleName])) {
                $io->warning($bundleName . ' is recommended, install it using composer req ' . $info['repo']);
            }
        }

        foreach ($bundles as $bundleName) {

        }

    }

    private function writeFile($fn, $contents) {
        file_put_contents($output = $this->projectDir . $fn, $contents);
        $this->io->success($fn . " written.");
    }
}
