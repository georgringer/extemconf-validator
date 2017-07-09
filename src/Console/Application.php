<?php

namespace GeorgRinger\ExtemconfValidator\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
                new InputDefinition(array(
                    new InputOption('file', 'f', InputOption::VALUE_REQUIRED),
                    new InputOption('cat', 'c', InputOption::VALUE_OPTIONAL),
                ))
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $file = $input->getOption('file');
        if (empty($file) || !file_exists($file)) {
            $io->warning(sprintf('File "%s" does not exist', $file));
        } else {

            $fileValidation = new \GeorgRinger\ExtemconfValidator\Validator();
            try {
                $fileValidation->validate($file);
                $io->success('all ok');
            } catch (\Exception $e) {
                $io->warning('ERROR:' . $e->getMessage());
            }
        }
    }
}
