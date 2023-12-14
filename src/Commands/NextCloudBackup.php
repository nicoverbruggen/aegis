<?php

namespace Aegis\Commands;

use Aegis\Aegis;
use League\Flysystem\Filesystem;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use function Aegis\config;

class NextCloudBackup extends Command
{
    protected static $defaultName = 'backup:nextcloud';

    protected function configure()
    {
        $this->setDescription("Runs all NextCloud backup jobs.");
        $this->addArgument('file', InputArgument::REQUIRED);
    }

    private function getNextcloudFileSystem(): Filesystem
    {
        $client = new Client([
            'baseUri' => config('NEXTCLOUD_BASE_URI'),
            'userName' => config('NEXTCLOUD_USERNAME'),
            'password' => config('NEXTCLOUD_PASSWORD'),
            'authType' => Client::AUTH_BASIC
        ]);

        $adapter = new WebDAVAdapter($client, 'remote.php/webdav/');

        return new Filesystem($adapter, ['case_sensitive' => false]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('file');

        try {
            $aegis = new Aegis($filename);
            $aegis->registerFilesystem('nextcloud', $this->getNextcloudFileSystem());
            $aegis->run($output, 'nextcloud');
        } catch (\Exception $ex) {
            $output->writeln("<error>{$ex->getMessage()}</error>");
            $output->writeln("{$ex->getTraceAsString()}");
            return -1;
        }

        return 0;
    }
}