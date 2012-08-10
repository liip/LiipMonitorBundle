# Liip Monitor Bundle #

This bundle provides a way to run a series of application related health checks. Health checks in the scope of this bundle go beyond simple actions like performing a _ping_ to a server to see if it's alive. For example a Memcache server can be alive and not displaying any errors in your Nagios but you might not be able to access it from your PHP application. Each health check should then implement some application logic that you want to make sure always works. Another usage can be testing for specific requirements, like availability of PHP extensions.

Another design goal of the bundle was to be able to perform the checks using the same configuration and environment that your application is using. In that way you can make sure that if the health check runs successfully then your app should work too.

So each health check will be a class that will implement the `CheckInterface::check` method which must return a `CheckResult` object. What happens inside that method
is up to the Check developer.

Health checks are defined as Symfony services and they have to be tagged as `monitor.check` in order to be picked up by the _Health Check Runner_. This gives a lot of flexibility to application and bundle developers when they want to add their own checks.

Checks are run via the command line using a Symfony command or via a REST api that delivers the results in JSON format.

Here's the web interface:

![Web Interface](http://img.skitch.com/20120312-fhyc74ese9jjpyd1wxjcdbs85b.jpg "Web Interface")

[![Build Status](https://secure.travis-ci.org/liip/LiipMonitorBundle.png)](http://travis-ci.org/liip/LiipMonitorBundle)

## Installation ##

Add the following code to your ```composer.json``` file:

And then run the Composer update command:

    $ php composer.phar update

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

## Writing Health Checks ##

Let's see an example on how to implement a Health Check class. In this case we are going to test for the availability of PHP Extensions:

    namespace Acme\HelloBundle\Check;

    use Liip\MonitorBundle\Check\Check;
    use Liip\MonitorBundle\Exception\CheckFailedException;
    use Liip\MonitorBundle\Result\CheckResult;

    class PhpExtensionsCheck extends Check
    {
        protected $extensions;

        public function __construct($extensions)
        {
            $this->extensions = $extensions;
        }

        public function check()
        {
            try {
                foreach ($this->extensions as $extension) {
                    if (!extension_loaded($extension)) {
                        throw new CheckFailedException(sprintf('Extension %s not loaded', $extension));
                    }
                }
                return $this->buildResult('OK', CheckResult::OK);
            } catch (\Exception $e) {
                return $this->buildResult(sprintf('KO - %s', $e->getMessage()), CheckResult::CRITICAL);
            }
        }

        public function getName()
        {
            return "PHP Extensions Health Check";
        }
    }


### CheckResult values ###

These values has been taken from the [nagios documentation](http://nagiosplug.sourceforge.net/developer-guidelines.html#RETURNCODES) :

 * ``CheckResult::OK`` - The plugin was able to check the service and it appeared to be functioning properly
 * ``CheckResult::WARNING`` - The plugin was able to check the service, but it appeared to be above some "warning" threshold or did not appear to be working properly
 * ``CheckResult::CRITICAL`` - The plugin detected that either the service was not running or it was above some "critical" threshold
 * ``CheckResult::UNKNOWN`` - Invalid command line arguments were supplied to the plugin or low-level failures internal to the plugin (such as unable to fork, or open a tcp socket) that prevent it from performing the specified operation. Higher-level errors (such as name resolution errors, socket timeouts, etc) are outside of the control of plugins and should generally NOT be reported as UNKNOWN states.


As you can see our constructor will take an array with the names of the extensions our application requires. Then on the `check` method it will iterate over that array to test for each of the extensions. If there are no problems then the check will return a `CheckResult` object with a message (`OK` in our case) and the result status (`CheckResult::SUCCESS` in our case). As you can see this is as easy as it gets.

Once you implemented the class then it's time to register the check service with our service container:

    services:
        monitor.check.php_extensions:
            class: Acme\HelloBundle\Check\PhpExtensionsCheck
            arguments:
                - [ xhprof, apc, memcache ]
            tags:
                - { name: monitor.check }

The important bit there is to remember to tag your services with the `monitor.check` tag. By doing that the Check Runner will be able to find your checks. Keep in mind that Checks can reside either in your bundles or in your app specific code. The location doesn't matter as long as the service is properly tagged.

## Running Checks ##

There are two ways of running the health checks: by using the CLI or by using the REST API provided by the bundle. Let's see what commands we have available for the CLI:

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

### Run Individual the Checks ###

To run an individual check you need to provide the check id to the `health` command:

    $ ./app/console monitor:health monitor.check.php_extensions

    PHP Extensions Health Check: OK

## REST API DOCS ##

For documentation on the REST API see: [http://myproject/monitor/health/](http://myproject/monitor/health/). Don't forget to add the bundle routes in your `routing.yml` file.

## LiipMonitorExtraBundle ##

We created a bundle where the community can share reusable health checks. We want this bundle to be a useful repository of health checks so go to the [https://github.com/liip/LiipMonitorExtraBundle](https://github.com/liip/LiipMonitorExtraBundle) and fork it to add your own.
