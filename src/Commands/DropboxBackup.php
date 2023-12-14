<?php

namespace Aegis\Commands;

use Aegis\Aegis;
use League\Flysystem\Filesystem;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Aegis\config;

class DropboxBackup extends Command
{
    protected static $defaultName = 'backup:dropbox';

    protected function configure()
    {
        $this->setDescription("Runs a Dropbox backup job.");
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    private function getDropboxFilesystem(): Filesystem
    {
        $client = new Client(config('DROPBOX_ACCESS_TOKEN'));
        $adapter = new DropboxAdapter($client);
        return new Filesystem($adapter, ['case_sensitive' => false]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');

        try {
            $aegis = new Aegis($filename);
            $aegis->registerFilesystem('dropbox', $this->getDropboxFilesystem());
            $aegis->run($output, 'dropbox');
        } catch (\Exception $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");
            $output->writeln("<error>{$ex->getTraceAsString()}</error>");
            return -1;
        }

        return 0;
    }
}