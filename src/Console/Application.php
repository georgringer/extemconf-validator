<?php

namespace GeorgRinger\ExtemconfValidator\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Application extends Command
{
    protected function configure()
    {
        $this
            ->setName('emconf:validate')
            ->setDescription('Validate ext_emconf.php files')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('file', 'f', InputOption::VALUE_REQUIRED),
                    new InputOption('cat', 'c', InputOption::VALUE_OPTIONAL),
                ])
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $file = $input->getOption('file');
        $io = new SymfonyStyle($input, $output);

        if (empty($path)) {
            $io->warning('No file given!');
            $io->warning('Using something like e.g.: emconf-validate emconf:validate --file=typo3conf/ext/');
            return false;
        }

        // single file
        if (substr_compare($path, 'ext_emconf.php', -strlen('ext_emconf.php')) === 0) {
            $io->title($this->getDescription());

            if (empty($file) || !file_exists($file)) {
                $io->warning(sprintf('File "%s" does not exist', $file));
            } else {
                try {
                    $fileValidation = new \GeorgRinger\ExtemconfValidator\Validator();
                    $fileValidation->validate($file);
                    $io->success('all ok');
                } catch (\Exception $e) {
                    $io->warning('ERROR:' . $e->getMessage());
                }
            }
        } elseif (is_dir($path)) {
            $path = rtrim($path, '/') . '/';
            $extensionList = array_diff(scandir($path), ['..', '.']);
            foreach ($extensionList as $extension) {
                $alternativePath = $path . $extension . '/ext_emconf.php';
                if (!file_exists($alternativePath)) {
                    continue;
                }
                $io->title($extension);
                try {
                    $fileValidation = new \GeorgRinger\ExtemconfValidator\Validator();
                    $fileValidation->validate($alternativePath);
                    $io->success('all ok');
                } catch (\Exception $e) {
                    $io->warning('ERROR:' . $e->getMessage());
                }
            }
        }
    }
}
