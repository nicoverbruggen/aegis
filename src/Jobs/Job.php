<?php

namespace Aegis\Jobs;

use League\Flysystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;

class Job
{
    private string $name;

    private string $source_path;
    private array $exclude;

    public string $destination_name;
    private string $destination_path;

    private ?OutputInterface $output = null;

    public function __construct($yaml)
    {
        $this->name = $yaml->name;
        $this->source_path = $yaml->source['path'];
        $this->exclude = $yaml->source['exclude'];
        $this->destination_name = $yaml->destination['name'];
        $this->destination_path = $yaml->destination['path'];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDriverName(): string
    {
        return $this->destination_name;
    }

    private function log($message): void
    {
        if ($this->output != null) {
            $this->output->writeln(date('Y/m/d H:i:s') . " | " . $message);
        }
    }

    /**
     * @param array<string, Filesystem> $filesystems
     * @param OutputInterface|null $output
     * @return void
     * @throws \Exception|\League\Flysystem\FilesystemException
     */
    public function execute(array $filesystems, ?OutputInterface $output): void
    {
        $this->output = $output;

        $this->log("Starting job {$this->name}");

        $excludeArguments = "--exclude='" . implode("' --exclude='", $this->exclude) . "'";
        $momentTimestamp = date('Y-m-d_His');

        $tempDirectory = __DIR__ . '/../../storage';
        $tempFile = "{$tempDirectory}/{$momentTimestamp}-{$this->name}.tar.gz";

        $this->log("- Will create gzipped tar in temporary folder...");

        // c = create, z = use gzip for compression, f = specify filename
        $command = "cd {$this->source_path} && tar $excludeArguments -czf $tempFile .";
        exec($command);

        $this->log("- [OK] Gzipped tar created in temporary folder!");
        $this->log("- Opening stream to temporary file...");

        $stream = $filesystems['local']->readStream($tempFile);

        if ( !$stream) {
            throw new \Exception("Stream could not be read.");
        }

        $this->log("- Writing stream to {$this->destination_name}...");

        $filesystems[$this->destination_name]->writeStream("{$this->destination_path}/{$momentTimestamp}-{$this->name}.tar.gz", $stream);

        $this->log("- [OK] Stream written!");

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->log("- Removing temporary file...");

        $filesystems['local']->delete("$tempFile");

        $this->log("- [OK] Temporary file has been removed!");
        $this->log("Job {$this->name} complete!");
    }
}