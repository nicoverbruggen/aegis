<?php

namespace Aegis;

use League\Flysystem\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use Aegis\Jobs\JobParser;
use Aegis\Jobs\Job;
use Symfony\Component\Console\Output\OutputInterface;

class Aegis
{
    private array $jobs;
    private array $filesystems;

    /**
     * Aegis constructor.
     * @param $filename
     * @throws \Aegis\Exceptions\YamlParserException
     */
    public function __construct($filename)
    {
        $this->jobs = (new JobParser($filename))->getJobs();
        $this->registerFilesystem('local', new Filesystem(new Local\LocalFilesystemAdapter('/')));
    }

    public function registerFilesystem(string $name, Filesystem $filesystem): void
    {
        $this->filesystems[$name] = $filesystem;
    }

    public function run(
        ?OutputInterface $output = null,
        string $filter = 'local'
    ) : bool {
        // Get all jobs
        $jobCount = count($this->jobs);
        $output->writeln("Parsed {$jobCount} jobs.");

        // Filter so only the desired type is executed
        $this->jobs = array_filter($this->jobs, fn ($job) => $job->getDriverName() == $filter);
        $jobCount = count($this->jobs);

        // Keep track of how many jobs must be executed
        $output->writeln("{$jobCount} jobs are valid for: `$filter`.");

        /** @var Job $job */
        foreach ($this->jobs as $job) {
            if (in_array($job->getDriverName(), array_keys($this->filesystems))) {
                $job->execute($this->filesystems, $output);
            }
        }

        return true;
    }
}