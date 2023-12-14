# Aegis
PHP package built to back up a locally tarred directory to a remote filesystem instance. This package is primarily used standalone as a terminal application on my own servers for backup purposes.

## System requirements
* PHP 8.3 or higher
* Network access

## Usage
You can use Aegis in two ways.

The **preferred method** allows you to run specific backups via the command line. The supported commands are:
  * `./aegis backup:dropbox <path-to-yaml>`
  * `./aegis backup:nextcloud <path-to-yaml>`
  * `./aegis backup:local <path-to-yaml>`

Alternatively, you can run Aegis in code and pointing at a YAML file, and running `$aegis->run()`. This requires you to set up your alternative filesystems manually.

## Job specification

The source must always be local (e.g. reachable on the local filesystem).
The destination can use any particular Filesystem instance, as long as it has been registered and the `filter` is set correctly when running `$aegis->run()`.

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
      name: nextcloud
      path: /
```

Notice that in this example file, the destination name is `nextcloud`, which means that running `./aegis backup:dropbox this-file.yaml` will not execute this job.

## Example usage

### Local

For Dropbox, set the access token first. You will need to authorize a separate app, which will normally write to its own app directory. Set the following keys in `.env`:

    DROPBOX_ACCESS_TOKEN=

Create one or more jobs in the YAML file, with a destination name of `dropbox`. The path can be customized.

When done, run the following command:

```
./aegis backup:dropbox <path-to-yaml>
```

### Nextcloud

For Nextcloud, set the WebDAV credentials first. You may need to make a separate app password. Set the following keys in `.env`:

    NEXTCLOUD_BASE_URI=
    NEXTCLOUD_USERNAME=
    NEXTCLOUD_PASSWORD=

Create one or more jobs in the YAML file, with a destination name of `nextcloud`. The path can be customized.

When done, run the following command:

```
./aegis backup:nextcloud <path-to-yaml>
```

## Getting started

### Initial setup

    git clone https://github.com/nicoverbruggen/aegis.git
    cd aegis
    composer install
    mkdir storage
    chmod +x ./aegis
    touch .env

For more information about how to configure Aegis to work with Dropbox or Nextcloud, please see **Example usage** above.

Finally, create the backup configuration file (YAML). You can place this file wherever you want, but for this example we're storing it in `./jobs`. You can find an example above.

    touch ./jobs/backup.yaml

Set up the `.env` configuration file as seen in the documentation, and then run the relevant command(s). Which one(s) to run depends on your jobs.

    ./aegis backup:nextcloud ./jobs/backup.yaml
    ./aegis backup:dropbox ./jobs/backup.yaml
    ./aegis backup:local ./jobs/backup.yaml

(There are plans to add a `./aegis backup:all` command but this is currently not available yet.)

## Limitations

* In the current version, Aegis can only source a directory from the local filesystem.
* There are no checks to see if enough space remains on the filesystem.
* You must provide an absolute path for the source (or override the `local` filesystem instance).
* No cleanup occurs, so you have to clean up your destination if there are too many backups in the directory.
  * The storage folder on the system performing the backup gets cleaned up after creating the tarball.
* This is purely a filesystem based backup, no database backup is included.
* Currently supported:
  * Nextcloud (as per v2.0)
  * Dropbox (as per v1.0)
  * Local filesystem (as per v1.0)