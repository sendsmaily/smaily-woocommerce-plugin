First off, thanks for taking the time to contribute!

# Table of contents

- [Getting started](#getting-started)
- [Internals](#internals)
    - [Structure of the repository](#structure-of-the-repository)
- [Development](#development)
    - [Starting the environment](#starting-the-environment)
    - [Stopping the environment](#stopping-the-environment)
    - [Resetting the environment](#resetting-the-environment)
- [Releasing](#releasing)


# Getting started

The development environment requires [Docker](https://docs.docker.com/) and [Docker Compose](https://docs.docker.com/compose/) to run. Please refer to the official documentation for step-by-step installation guide.

Clone the repository:

    $ git clone git@github.com:sendsmaily/smaily-woocommerce-plugin.git

Next, change your working directory to the local repository:

    $ cd smaily-woocommerce-plugin

And run the environment:

    $ docker-compose up


# Internals

## Structure of the repository

The repository is split into multiple parts:

- `assets` - screenshots for Wordpress.org plugin page;
- `inc` - classes for providing core and content functionality;
- `lang` - localization files;
- `static` - CSS, images and Javascript for admin panel and public pages;
- `templates` - admin panel and public page templates;
- `vendor` - Composer libraries;

In addition there are system directories:

- `.github` - GitHub issue and pull request templates;


# Development

All code written must follow [Wordpress' Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/). Including CSS, HTML, Javascript and PHP.

## Starting the environment

You can run the environment by executing:

    $ docker-compose up

> **Note!** Make sure you do not have any other process(es) listening on ports 8080 and 8888.

## Stopping the environment

Environment can be stopped by executing:

    $ docker-compose down

## Resetting the environment

If you need to reset the installation, just simply delete environment's Docker volumes. Easiest way to achieve this is by running:

    $ docker-compose down -v


# Releasing

Releasing a new version of the plugin to Wordpress.org requires [SVN client](https://subversion.apache.org/packages.html).

After creating a release in GitHub, plugin must also be deployed to Wordpress.org. The repository contains a handy script for that:

    $ ./release.sh -u [Your Wordpress.org username]

> It will guide you through the release process step-by-step.
