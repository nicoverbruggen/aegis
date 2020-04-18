<?php

namespace Aegis;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Aegis\Jobs\JobParser;
use Aegis\Jobs\Job;
use Symfony\Component\Console\Output\OutputInterface;

class Aegis
{
    private array $jobs;
    private array $filesystems;
    private MountManager $manager;

    /**
     * Aegis constructor.
     * @param $filename
     * @throws \Aegis\Exceptions\YamlParserException
     */
    public function __construct($filename)
    {
        $this->manager = new MountManager();
        $this->jobs = (new JobParser($filename))->getJobs();

        $this->registerFilesystem(
            'local',
            new Filesystem(new Local('/'))
        );
    }

    public function registerFilesystem(string $name, Filesystem $filesystem)
    {
        $this->filesystems[] = $name;
        $this->manager->mountFilesystem($name, $filesystem);
    }

    public function run(?OutputInterface $output = null) : bool
    {
        /** @var Job $job */
        foreach ($this->jobs as $job) {
            $job->execute($this->manager, $output);
        }

        return true;
    }
}