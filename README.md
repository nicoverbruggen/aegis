# Aegis
A package built to back up a locally tarred directory to a remote filesystem instance.
Can also be used standalone as a terminal application.

## Usage

You can use Aegis in two ways:

* By creating a new instance of Aegis in code and pointing at a YAML file, and running $aegis->
* By invoking `./aegis backup:local` or `./aegis backup:dropbox` and pointing at a YAML file

## Limitations

* In the current version, Aegis can only backup a directory on the local filesystem.
* There are no checks to see if enough space remains on the filesystem.
* You must provide an absolute path for the source (or override the `local` filesystem instance).
* No cleanup occurs, so you have to clean up your destination.

## Job specification

The source must ALWAYS be local.
The destination can use any particular Filesystem instance, as long as it has been registered.

Example file:
```yaml
jobs:
  nicoverbruggen.be:
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

