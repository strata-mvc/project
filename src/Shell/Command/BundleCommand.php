<?php
namespace App\Shell\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The bundle command will ensure the application is correctly configured
 * when the project is first setup.
 */
class BundleCommand extends \Strata\Shell\Command\StrataCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('bundle')
            ->setDescription('Bundles the project frontend files.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startup($input, $output);
        $this->bundleThemesFrontend();
        $this->shutdown();
    }

    private function bundleThemesFrontend()
    {
        foreach ($this->getThemesDirectories() as $themePath) {
            $this->bundleFrontend($themePath);
        }
    }

    private function getThemesDirectories()
    {
        return glob("web/app/themes/*/");
    }

    /**
     * Goes in the directory and bundles the frontend tools
     * @param  string $themePath
     */
    private function bundleFrontend($themePath)
    {
        $this->output->writeln("Creating frontend bundle for <info>$themePath</info>");
        exec("cd $themePath && npm install && bower install && grunt dist");
        $this->nl();
    }
}
