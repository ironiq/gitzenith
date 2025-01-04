# GitZenith: an elegant git repository viewer

GitZenith is an elegant and modern web interface for interacting with multiple git repositories. It allows you to browse repositories using your favorite browser, viewing files under different revisions, commit history, diffs. It also generates RSS/Atom feeds for each repository, allowing you to stay up-to-date with the latest changes anytime, anywhere. GitZenith is written in PHP, on top of the [Symfony](https://symfony.com) framework and powered by the Twig template engine. This means that GitZenith is easy to install and easy to customize. Also, the GitZenith interface was made possible due to [Bootstrap](https://getbootstrap.com).

## Features

* Multiple repository support
* Multiple branch support
* Multiple tag support
* Commit history, blame, diff
* RSS/Atom feeds
* Syntax highlighting via CodeMirror or Ace
* Repository statistics

## Requirements

In order to run GitZenith on your server, you'll need:

* PHP 8.2
* git 2
* Webserver (Apache, nginx)

## Installation

* Download the [latest release](https://github.com/ironiq/gitzenith/releases) and decompress to your `/var/www/gitzenith` folder, or anywhere else you want to place GitZenith.
  * Do not use the source release, or download a branch or tag from GitHub. It is not suited for end-users, only development.
* Open up the `config/config.yml` and configure your installation. You'll have to provide where your repositories are located.
  * Alternatively, you can export the environment variable `DEFAULT_REPOSITORY_DIR` with the directory containing your repositories
* Create the cache and log folder and give it read/write permissions to your web server user:

```bash
cd /var/www/gitzenith
mkdir -p var/cache
chmod 777 var/cache
mkdir -p var/log
chmod 777 var/log
```

* Point your webserver to the `/var/www/gitzenith/public` folder, where `index.php` is.

That's it, installation complete! If you're having problems, check the [Troubleshooting](https://github.com/ironiq/gitzenith/blob/main/docs/Troubleshooting.md) page.

## Development

GitZenith comes with a Docker Compose configuration intended for development purposes. It contains a PHP image with all necessary extensions, as well as a Node image for frontend assets.

To get started, just clone the repo and run the setup script:

```bash
git clone https://github.com/ironiq/gitzenith.git
make setup
```

It should take care of letting you know what is missing, if anything. Once finished, run the test suite to make sure everything is in order:

```bash
make test
make acceptance
```

There are other commands available. To learn more:

```bash
make help
```

## Contributing

If you are a developer, we need your help. GitZenith is small, but we have lots of stuff to do. Some developers are contributing with new features, others with bug fixes. But you can also dedicate yourself to refactoring the current codebase and improving what we already have. This is very important, we want GitZenith to be a state-of-the-art application, and we need your help for that.

* Stay tuned to possible bugs, suboptimal code, duplicated code, overcomplicated expressions and unused code
* Improve the test coverage by creating unit and functional tests

## Further information

If you want to know more about customizing GitZenith, check the [Customization](https://github.com/ironiq/gitzenith/blob/main/docs/Customizing.md) page on the wiki. Also, if you're having problems with GitZenith, check the [Troubleshooting](https://github.com/ironiq/gitzenith/blob/main/docs/Troubleshooting.md) page. Don't forget to report issues and suggest new features! :)

## Legacy

GitZenith was born in [May 2012](https://github.com/klaussilveira/gitlist/commit/df43c987cf02a3521ac65cf5bd4a4f54cf749177) as GitList, a time were Composer was still a novelty and Silex was all the rage.

The origin of this project, however, is still [available here](https://github.com/klaussilveira/gitlist).
