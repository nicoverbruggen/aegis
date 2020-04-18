# Aegis
A package built to back up a locally tarred directory to a remote filesystem instance.
Can also be used standalone as a terminal application.

## Usage
You can use Aegis in two ways:

* By creating a new instance of Aegis in code and pointing at a YAML file, and running `$aegis->run()`
* By invoking `./aegis backup:local` or `./aegis backup:dropbox` and pointing at a YAML file

## Job specification

The source must always be local (e.g. reachable on the local filesystem).
The destination can use any particular Filesystem instance, as long as it has been registered.

Example file:
```yaml
jobs:
  your-website-name:
    source:
      path: /var/www/path-to-your-website
      exclude:
          - ./.git
          - ./vendor
          - ./node_modules
          - ./storage/framework
    destination:
      name: dropbox
      path: /
```

## Example usage

    chmod +x ./aegis
    echo "DROPBOX_ACCESS_TOKEN={your-code-here}" > .env
    touch ./jobs/backup.yaml
    
Set up the configuration as seen in the documentation, and then run:    
    
    ./aegis backup:dropbox ./jobs/backup.yaml

## Limitations

* In the current version, Aegis can only backup a directory on the local filesystem.
* There are no checks to see if enough space remains on the filesystem.
* You must provide an absolute path for the source (or override the `local` filesystem instance).
* No cleanup occurs, so you have to clean up your destination. (The storage folder gets cleaned up after creating the tarball.)
* This is purely a filesystem based backup, no database backup is included.
* Only Dropbox and the local filesystem are supported out of the box when using the CLI.