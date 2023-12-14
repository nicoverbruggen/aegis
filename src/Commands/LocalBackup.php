<?php

namespace Aegis\Commands;

use Aegis\Aegis;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class LocalBackup extends Command
{
    protected static $defaultName = 'backup:locally';

    protected function configure()
    {
        $this->setDescription("Runs a local backup job.");
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');

        try {
            $aegis = new Aegis($filename);
            $aegis->run($output, 'local');
        } catch (\Exception $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");
            $output->writeln("<error>{$ex->getTraceAsString()}</error>");
            return -1;
        }

        return 0;
    }
}