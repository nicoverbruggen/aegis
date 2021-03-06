<?php

namespace Aegis\Jobs;

use League\Flysystem\MountManager;
use Symfony\Component\Console\Output\OutputInterface;

class Job
{
    private string $name;

    private string $source_path;
    private array $exclude;

    private string $destination_name;
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

    private function log($message)
    {
        if ($this->output != null) {
            $this->output->writeln(date('Y/m/d H:i:s') . " | " . $message);
        }
    }

    public function execute(MountManager $manager, ?OutputInterface $output)
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

        $stream = $manager->readStream("local://$tempFile");

        if ( !$stream) {
            throw new \Exception("Stream could not be read.");
        }

        $this->log("- Writing stream to {$this->destination_name}...");

        $manager->writeStream(
            "{$this->destination_name}://{$this->destination_path}/{$momentTimestamp}-{$this->name}.tar.gz",
            $stream
        );

        $this->log("- [OK] Stream written!");

        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->log("- Removing temporary file...");

        $manager->delete("local://$tempFile");

        $this->log("- [OK] Temporary file has been removed!");
        $this->log("Job {$this->name} complete!");
    }
}