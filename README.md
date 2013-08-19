# Liip Monitor Bundle #

This bundle provides a way to run a series of application related health checks.
Health checks in the scope of this bundle go beyond simple actions like performing
a _ping_ to a server to see if it's alive. For example a Memcache server can be
alive and not displaying any errors in your Nagios but you might not be able to
access it from your PHP application. Each health check should then implement some
application logic that you want to make sure always works. Another usage can be
testing for specific requirements, like availability of PHP extensions.

Another design goal of the bundle was to be able to perform the checks using the
same configuration and environment that your application is using. In that way you
can make sure that if the health check runs successfully then your app should work
too.

So each health check will be a class that will implement the `CheckInterface::check`
method which must return a `CheckResult` object. What happens inside that method
is up to the check developer.

Health checks are defined as Symfony services and they have to be tagged as
`liip_monitor.check` in order to be picked up by the _health check runner_. This gives
a lot of flexibility to application and bundle developers when they want to add their
own checks.

Checks are run via the command line using a Symfony command or via a REST api that
delivers the results in JSON format.

Here's the web interface:

![Web Interface](http://img.skitch.com/20120312-fhyc74ese9jjpyd1wxjcdbs85b.jpg "Web Interface")

## Installation ##

Add the following code to your ```composer.json``` file:

    "require": {
        ..
        "liip/monitor-bundle": "dev-master"
    },

And then run the Composer update command:

    $ php composer.phar update liip/monitor-bundle

Then register the bundle in the `AppKernel.php` file:

    public function registerBundles()
    {
        $bundles = array(
            ...
            new Liip\MonitorBundle\LiipMonitorBundle(),
            ...
        );

        return $bundles;
    }

If you want to enable the REST API provided by the bundle then add the following to your `routing.yml`:

    _monitor:
        resource: "@LiipMonitorBundle/Resources/config/routing.yml"
        prefix: /monitor/health

And finally don't forget to install the bundle assets into your web root:

    $ ./app/console assets:install web --symlink --relative

## Enabling built-in health checks

To enable built-in health checks, add them to your `config.yml`


```yml
liip_monitor:
    checks:
        php_extensions: [apc, xdebug]
```
## Adding Health Checks ##

Once you implemented the class then it's time to register the check service with our service container:

    services:
        monitor.check.php_extensions:
            class: Acme\HelloBundle\Check\PhpExtensionsCheck
            arguments:
                - [ xhprof, apc, memcache ]
            tags:
                - { name: liip_monitor.check, alias: php_extensions }

The important bit there is to remember to tag your services with the `liip_monitor.check` tag.
By doing that the check runner will be able to find your checks. Keep in mind that checks
can reside either in your bundles or in your app specific code. The location doesn't matter
as long as the service is properly tagged. The ``alias`` is optional and will then simply
define the ``id`` used when running health checks individually, otherwise the full service
id must be used in this case.

## Check groups ##

All built-in checks are assigned to the group `default`.
See [check groups](https://github.com/liip/LiipMonitor/tree/master#check-groups) in the `LiipMonitor` library for details.


## Available Health Checks ##

On top of all the checks provided by the LiipMonitor library this Bundle adds the following
Symfony2 specific health checks:

### CustomErrorPagesCheck ###

Checks if error pages have been customized for given error codes.

### DepsEntriesCheck ###

Checks all entries from `deps` are defined in `deps.lock`.

### SymfonyVersionCheck ###

Checks the version of this website against the latest stable release.

### Running Checks ###

There are two ways of running the health checks: by using the CLI or by using the REST API
provided by the bundle. Let's see what commands we have available for the CLI:

### List Checks ###

    $ ./app/console monitor:list

    monitor.check.jackrabbit
    monitor.check.redis
    monitor.check.memcache
    monitor.check.php_extensions

### Run All the Checks ###

    ./app/console monitor:health

    Jackrabbit Health Check: OK
    Redis Health Check: OK
    Memcache Health Check: KO - No configuration set for session.save_path
    PHP Extensions Health Check: OK

### Run Individual Checks ###

To run an individual check you need to provide the check id to the `health` command:

    $ ./app/console monitor:health monitor.check.php_extensions

    PHP Extensions Health Check: OK


### Run health checks as composer post-install/update scripts

To run health checks as a composer post-install or post-update script, simply add the
`Liip\\MonitorBundle\\Composer\\ScriptHandler::checkHealth` ScriptHandler to the 
`post-install-cmd / post-update-cmd` command sections of your `composer.json`:

``` json
    "scripts": {
        "post-install-cmd": [
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
            "Liip\\MonitorBundle\\Composer\\ScriptHandler::checkHealth"
        ],
        "post-update-cmd": [
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
            "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
            "Liip\\MonitorBundle\\Composer\\ScriptHandler::checkHealth"
        ]
    },


```

## REST API DOCS ##

For documentation on the REST API see: [http://myproject.org/monitor/health/](http://myproject.org/monitor/health/).
Don't forget to add the bundle routes in your `routing.yml` file.


## Nagios integration ##

You can find a simple Nagios check written in Perl and Python in the Resources/scripts directory.

### Perl Version ###

This is dependent on perl modules available on CPAN Getopt::Std, WWW::Mechanize, and JSON


Copy the script into your scripts directory in Nagios and create a command like this:

    define command{
            command_name    check_symfony_health
            command_line    $USER1$/check_symfony2.pl -H $HOSTNAME$
    }

Running the command with the Hostname flag (-H) will check "http://$HOSTNAME$/monitor/health/run".
You can also use the Address flag (-A) to check a specified URL: 

    command_line    $USER1$/check_symfony2.pl -A https://mysite.org/monitor/health/run

The plugin can be used with Authentication, Using the Username (-u) and Password (-p) flags:

    command_line    $USER1$/check_symfony2.p1 -H $HOSTNAME$ -u username -p password

You can also specify the Warning (-w) and Critical (-c) levels for the check using the standard flags

    command_line    $USER1$/check_symfony2.pl -H $HOSTNAME$ -w 1 -c 2

Any flags can be combined except -A and -H. THe -u and -p flags should always be used together.

### Python Version ###

The Python version depends on the nagiosplugin library < 1.0.0.

Copy the script into your scripts directory in Nagios and create a command like this:

    define command{
            command_name    check_symfony_health
            command_line    $USER1$/check_symfony2.py -w 0  -c 0 -u https://$HOSTNAME$
    }

To use the plugin with HTTP basic authentication, change the command to:

    command_line    $USER1$/check_symfony2.py -w 0  -c 0 -u https://$HOSTNAME$ -a username:password

### Connecting Check to Host in Nagios ###

Add a service:

    define service{
     hostgroup_name         Symfony2
     service_description    Symfony2 health check
     check_command          check_symfony_health
     use                    generic-service
    }

And create a host attached to the Symfony2 hostgroup:

    define host{
        use              web-host
        host_name        www.myhost.com
        address          8.8.8.4
        hostgroups       Symfony2
    }

And place your host within the Symfony2 hostgroup.

