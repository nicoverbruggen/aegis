<?php

namespace Aegis\Jobs;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use Aegis\Exceptions\YamlParserException;

class JobParser
{
    /** @var array<Job> $jobs */
    private array $jobs = [];

    /** @throws YamlParserException */
    public function __construct($path) {

        if (!file_exists($path)) {
            throw new YamlParserException("The path to the configuration file is invalid");
        }

        try {
            $config = Yaml::parse(file_get_contents($path));
        } catch (ParseException $ex) {
            throw new YamlParserException("You've provided an invalid YAML file");
        }

        if (!array_key_exists('jobs', $config)) {
            throw new YamlParserException("You must specify a jobs list (`jobs`)");
        }

        $jobNames = array_keys($config['jobs']);

        if (count($jobNames) == 0) {
            throw new YamlParserException("You have not specified any jobs.");
        }

        foreach ($jobNames as $jobName) {
            $object = (object)$config['jobs'][$jobName];
            $object->name = $jobName;

            $keys = [
                'source' => ['path', 'exclude'],
                'destination' => ['name', 'path']
            ];

            foreach ($keys as $property => $expectedKeys) {
                foreach ($expectedKeys as $expectedKey) {
                    if (!array_key_exists($expectedKey, $object->{$property})) {
                        throw new YamlParserException("The key $property.$expectedKey is missing from the job.");
                    }
                }
            }

            $this->jobs[] = new Job($object);
        }
    }

    public function getJobs() : array
    {
        return $this->jobs;
    }
}