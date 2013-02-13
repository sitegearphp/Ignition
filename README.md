# Sitegear Ignition

The Sitegear Ignition package is used to generate the necessary boilerplate for a Sitegear website.

This is a "turn-key" solution for creating a website using Sitegear (pun intended).

**NOTE** This is a work in progress.  At the moment you should expect _nothing_ to work!

## Distribution

In general the "master" script at http://sitegear.org/ignition/resources/ignition.php should be used.

This repository is available for those wishing to browse the code.

## Usage

To create a skeleton website with the necessary boilerplate to start working in Sitegear, follow these simple steps:

 1. Start in an empty directory where your website will be created:

        cd /path/to/webroot  # e.g. cd /var/www
        mkdir mywebsite.com
        cd mywebsite.com

 2. Download and execute the ignition script:

        php -r "eval('?>'.file_get_contents('http://sitegear.org/ignition/resources/ignition.php'));"

    Note that due to the interactive nature of the script, the method `curl -s [url] | php` does not work; the pipe `|`
    breaks the ability to receive user input.

 3. Answer the questions when prompted.

 4. Watch as the relevant magic is applied.

 5. Visit the site in a web browser (e.g. `http://localhost/mywebsite.com/`).

## Why is this not on Packagist?

This is an important question.  Conceptually, the Ignition package does not belong on Packagist.

The main Sitegear library is on Packagist because it is a dependency of your website.

The Ignition package is _not_ a dependency of your website.  It is used _only_ to build the initial contents of your
website, after that there is no way of telling whether the site was created by the ignition package or by some other
method.

Since the Ignition package is not a dependency, it should not be included through Composer, the dependency manager,
which is the only reason to put something on Packagist.
