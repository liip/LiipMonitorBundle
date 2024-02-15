# liip/monitor-bundle

This bundle provides a way to run a series of application related health checks.

## Upgrading from 2.x to 3.x

Version 3.x is a complete rewrite of the bundle using modern Symfony features. The following
changes have been made:

1. We now provide our own `Check` and `Result` system.
2. The web interface has been removed.
3. `laminas/laminas-diagnostics` is now an optional dependency (a [_bridge_](#laminas-diagnostics-check-bridge) is available).
4. _Check Groups_ have been renamed to [_Check Suites_](#check-suites).
5. Option to run your checks with [`symfony/messenger`](#symfony-messenger) and/or [`symfony/scheduler`](#symfony-scheduler).
6. [System and Info services](#system-and-info-services) have been added.

## Installation

```bash
composer require liip/monitor-bundle
```

## Enable/Create Checks

### Packaged Checks

The packaged checks are enabled via the bundle config. See [Full Default Configuration](#full-default-configuration)
for descriptions for each check. Here is an example showing the default options when simply enabled:

```yaml
# config/packages/liip_monitor.yaml

liip_monitor:
    checks:
        system_memory_usage: true # warn @ 70%, fail @ 90%

        system_disk_usage: true # warn @ 70%, fail @ 90%

        # requires configuration
        system_free_disk_space:
            warning: 20GB
            critical: 10GB

        system_reboot: true

        system_load_average:
            1_minute: true # warn @ 70%, fail @ 90%
            5_minute: true # warn @ 70%, fail @ 90%
            15_minute: true # warn @ 70%, fail @ 90%

        apcu_memory_usage: true # warn @ 70%, fail @ 90%

        apcu_fragmentation: true # warn @ 70%, fail @ 90%

        opcache_memory_usage: true # warn @ 70%, fail @ 90%

        php_version: true

        composer_audit: true

        symfony_version: true

        dbal_connection: true # auto creates a check for each dbal connection
        dbal_connection: default # use specific dbal connection
        dbal_connection: [default, alternate] # use specific dbal connections

        # requires configuration
        ping_url:
            Server1: https://www.example.com # ensures a 2xx response
            Server2:
                url: https://www.example.com
                expected_status_code: 204 # ensures a 204 response
            Server3:
                url: https://www.example.com
                warning_duration: 200 # triggers a warning if the response takes longer than 200ms
                critical_duration: 1000 # triggers a failure if the response takes longer than 1s
            Server4:
                url: https://www.example.com
                expected_content: "foo" # fails if "foo" is not found in the response body
```

### Custom Checks

You can create your own checks by having an autoconfigured service implement
`Liip\Monitor\Check`:

```php
namespace App\Check;

use Liip\Monitor\Check;
use Liip\Monitor\Result;

class SomeServiceCheck implements Check
{
    public function run(): Result
    {
        if ($condition) {
            return Result::failure('summary message');
            return Result::failure('message summary', 'detailed message', ['some' => 'context']);
        }

        if ($anotherCondition) {
            return Result::warning('summary message');
            return Result::warning('message summary', 'detailed message', ['some' => 'context']);
        }

        return Result::success();
        return Result::success('message summary', 'detailed message', ['some' => 'context']);

        // other result statuses
        return Result::skip('summary message')
        return Result::unknown('summary message')
    }
}
```

> [!NOTE]
> By default, the class name is used to determine the check label (above, the label would be _Some Service_).
> This [can be customized](#check-labels) by making the check `\Stringable` _or_ with the `AsCheck` attribute.

> [!NOTE]
> Use the `AsCheck` attribute to configure [caching](#caching-check-results),
> [check id](#check-ids), and [suites](#check-suites).

> [!NOTE]
> If `run()` throws an exception, it will be caught and converted to an _error_ result when running.

#### Laminas Diagnostics Check Bridge

You can also have autoconfigured services implement `Laminas\Diagnostics\Check\CheckInterface` if
you wish to use the [laminas/laminas-diagnostics](https://github.com/laminas/laminas-diagnostics) check
system:

```php
namespace App\Check;

use Laminas\Diagnostics\Check\CheckInterface;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Laminas\Diagnostics\Result\Warning;
use Laminas\Diagnostics\Result\Failure;

class MyCheck implements CheckInterface
{
    public function getLabel(): string
    {
        return 'My Check';
    }

    public function check(): ResultInterface
    {
        if ($condition) {
            new Failure('summary message');
        }

        if ($anotherCondition) {
            new Warning('summary message');
        }

        return new Success('summary message');
    }
}
```

> [!NOTE]
> Use the `AsCheck` attribute to configure [caching](#caching-check-results),
> [check id](#check-ids), and [suites](#check-suites).

> [!NOTE]
> `laminas/laminas-diagnostics` is an optional dependency. You must install it yourself.

### Caching Check Results

The check results can be cached. This can be useful if using a service that frequently
hits and endpoint on your app that runs all your checks.

The default cache TTL can be configured globally:

```yaml
# config/packages/liip_monitor.yaml

liip_monitor:
    default_ttl: 60 # 60 seconds
```

For the [packaged checks](#packaged-checks), the ttl can be configured per check:

```yaml
liip_monitor:
    checks:
        system_memory_usage:
            ttl: 60 # 60 seconds

        system_disk_usage:
            ttl: -1 # disable caching for this check
```

For [custom checks](#custom-checks), use the `AsCheck` attribute:

```php
use Liip\Monitor\AsCheck;
use Liip\Monitor\Check;

#[AsCheck(ttl: 60)]
class SomeServiceCheck implements Check
{
}

#[AsCheck(ttl: AsCheck::DISABLE_CACHE)] // disable caching for this check
class SomeServiceCheck implements Check
{
}
```

### Check Suites

You can _group_ your checks into _check suites_. Suites can be run individually.

For the [packaged checks](#packaged-checks), suites can be configured in the config:

```yaml
liip_monitor:
    checks:
        system_memory_usage:
            suite: system

        system_disk_usage:
            suite: [system, disk] # multiple suites
```

For [custom checks](#custom-checks), use the `AsCheck` attribute:

```php
use Liip\Monitor\AsCheck;
use Liip\Monitor\Check;

#[AsCheck(suite: 'system')]
class SomeServiceCheck implements Check
{
}

#[AsCheck(suite: ['system', 'database'])] // multiple suites
class SomeServiceCheck implements Check
{
}
```

### Check Labels

For the [packaged checks](#packaged-checks), the label can be overridden in the config:

```yaml
# config/packages/liip_monitor.yaml

liip_monitor:
    checks:
        system_memory_usage:
            label: 'My Label'
```

For [custom checks](#custom-checks), there are two options to customize the label:

1. Make your check class `\Stringable`:
    ```php
    use Liip\Monitor\Check;

    class SomeServiceCheck implements Check, \Stringable
    {
        public function __toString(): string
        {
            return 'My Label';
        }
    }
    ```
2. Use the `AsCheck` attribute:
    ```php
    use Liip\Monitor\AsCheck;
    use Liip\Monitor\Check;

    #[AsCheck(label: 'My Label')]
    class SomeServiceCheck implements Check
    {
    }
    ```

### Check IDs

Each check must have a unique identifier. These are used for running individual checks.
They are generated based on the [check label](#check-labels) but can be customized.

For the [packaged checks](#packaged-checks), the custom id can be set in the config:

```yaml
# config/packages/liip_monitor.yaml

liip_monitor:
    checks:
        system_memory_usage:
            id: my_custom_id
```

For [custom checks](#custom-checks), use the `AsCheck` attribute:

```php
use Liip\Monitor\AsCheck;
use Liip\Monitor\Check;

#[AsCheck(id: 'my_custom_id')]
class SomeServiceCheck implements Check
{
}
```

## List Checks

### `monitor:list` Console Command

Use the `monitor:list` command to list all configured checks. This will show their
ID, label, any suites they are part of and the configured result cache TTL.

```bash
bin/console monitor:list
```

> [!NOTE]
> Add this command to your CI pipeline to ensure all checks can be instantiated before deploying.

### Manually Listing Checks

Inject the `CheckRegistry` service into your own service/controller:

```php
use Liip\Monitor\Check\CheckRegistry;

class ListChecksController
{
    public function __invoke(CheckRegistry $registry): Response
    {
        $checks = $registry->suite()->checks();
        $checks = $registry->suite('database')->checks(); // just the "database" suite

        foreach ($all as $check) {
            /** @var Liip\Monitor\Check\CheckContext $check */
            $check->id(); // string - unique id for check
            $check->suites(); // string[] - suites this check is part of
            $check->ttl(); // int|null - cache ttl for check results
            $check->__toString(); // string - label for check
            $check->wrapped(); // the "real" check implementation
        }

        // ...
    }
}
```

You can alternatively inject the `CheckSuite` directly:

```php
use Liip\Monitor\Check\CheckSuite;

class ListChecksController
{
    public function __invoke(
        CheckSuite $checks, // "all" checks
        CheckSuite $databaseChecks, // just the "database" suite
    ): Response {
        // ...
    }
}
```

## Run Checks

### `monitor:health` Console Command

Use the `monitor:health` command to run checks:

```bash
bin/console monitor:health # runs all configured checks
bin/console monitor:health --suite=database # runs all checks in the "database" suite
bin/console monitor:health 3d6c988d # runs check with id "3d6c988d" (find ID's in monitor:list)
bin/console monitor:health --no-cache # runs all checks with caching disabled
```

By default, the command only fails (exit code `1`) if any check results are _failures_ or _errors_.
You can customize this behaviour:

```bash
bin/console monitor:health --fail-on-warning # fails if any results are warnings, failures or errors
bin/console monitor:health --fail-on-skip # fails if any results are skipped, failures or errors
bin/console monitor:health --fail-on-unknown # fails if any results are unknown, failures or errors
```

### Symfony Messenger

Messages and handlers are provided to run checks asynchronously with `symfony/messenger`:

```php
use Liip\Monitor\Messenger\RunChecks;
use Liip\Monitor\Messenger\RunCheck;
use Liip\Monitor\Messenger\RunCheckSuite;

/** @var \Symfony\Component\Messenger\MessageBusInterface $bus */

$bus->dispatch(new RunCheckSuite()); // run all checks
$bus->dispatch(new RunCheckSuite(cache: false)); // run all checks with caching disabled
$bus->dispatch(new RunCheckSuite(suite: 'database')); // run all checks in the "database" suite

$bus->dispatch(new RunChecks(['id-1', 'id-2'])); // run a set of specific checks
$bus->dispatch(new RunChecks(['id-1', 'id-2'], cache: false)); // run a set of specific checks with caching disabled

$bus->dispatch(new RunCheck('id')); // run a specific check
$bus->dispatch(new RunCheck('id'), cache: false); // run a specific check with caching disabled
```

### Symfony Scheduler

Use the messages above with `symfony/scheduler` to run checks/check suites on a schedule:

```php
use Liip\Monitor\Messenger\RunChecks;
use Liip\Monitor\Messenger\RunCheck;
use Liip\Monitor\Messenger\RunCheckSuite;

#[AsSchedule('default')]
class DefaultScheduleProvider implements ScheduleProviderInterface
{
    public function getSchedule(): Schedule
    {
        return (new Schedule())->add(
            RecurringMessage::every('1 day', new RunCheckSuite()), // run all checks every day
            RecurringMessage::every('1 day', new RunCheckSuite(cache: false)), // run all checks every day with caching disabled
            RecurringMessage::every('1 day', new RunCheckSuite('database')), // run "database" check suite every day
            RecurringMessage::every('1 day', new RunCheck('id')), // run check with id "id" every day
            RecurringMessage::every('1 day', new RunChecks(['id-1', 'id-2'])), // run checks with ids "id-1" and "id-2" every day
        );
    }
}
```

### Manually Run Check Suites

Inject the `CheckRegistry` service into your own service/controller:

```php
use Liip\Monitor\Check\CheckRegistry;

class ListChecksController
{
    public function __invoke(CheckRegistry $registry): Response
    {
        $suite = $registry->suite();
        $suite = $registry->suite('database'); // just the "database" suite

        $results = $suite->run(); // Liip\Monitor\Result\ResultSet
        $results = $suite->run(cache: false); // disable caching for this run

        $results->count();
        $results->duration(); // float
        $results->all(); // ResultContext[]

        $results->successes(); // Liip\Monitor\Result\ResultSet
        $results->failures(); // Liip\Monitor\Result\ResultSet
        $results->errors(); // Liip\Monitor\Result\ResultSet
        $results->warnings(); // Liip\Monitor\Result\ResultSet
        $results->skipped(); // Liip\Monitor\Result\ResultSet
        $results->unknowns(); // Liip\Monitor\Result\ResultSet
        $results->defects(); // Liip\Monitor\Result\ResultSet (errors + failures)
        $results->defects(Status::WARNING); // Liip\Monitor\Result\ResultSet (warnings + errors + failures)
        $results->notOfStatus(Status::SUCCESS); // Liip\Monitor\Result\ResultSet (all but successes)

        // ...
    }
}
```

You can alternatively inject the `CheckSuite` directly:

```php
use Liip\Monitor\Check\CheckSuite;

class ListChecksController
{
    public function __invoke(
        CheckSuite $checks, // "all" checks
        CheckSuite $databaseChecks, // just the "database" suite
    ): Response {
        // ...
    }
}
```

### Manually Run Individual Checks

Inject the `CheckRegistry` service into your own service/controller:

```php
use Liip\Monitor\Check\CheckRegistry;

class RunCheckController
{
    public function __invoke(CheckRegistry $registry, string $checkId): Response
    {
        try {
            $check = $registry->get($checkId);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException('Check not found.');
        }

        $result = $check->run(); // Liip\Monitor\Result\ResultContext
        $result = $check->run(cache: false); // disable caching for this run

        $result->status(); // Liip\Monitor\Result\Status
        $result->duration(); // float
        $result->summary(); // string
        $result->detail(); // string
        $result->check(); // Liip\Monitor\Check\CheckContext

        // ...
    }
}
```

## System and Info Services

These services can be useful for creating admin dashboard _widgets_ to show system status.

### `System`

A `System` (autowire-able) service is provided to get information about the system the app is running on:

```php
/** @var \Liip\Monitor\System $system */

(string) $system; // OS info (ie Ubuntu 18.04.6 LTS)
(string) $system->webserver(); // web server info (ie nginx/1.18.0)
$system->isRebootRequired(); // bool
[$oneMinute, $fiveMinute, $fifteenMinute] = $system->loadAverages(); // Percent[]

$disk = $system->disk(); // StorageInfo
$disk->free(); // Zenstruck/Bytes
$disk->used(); // Zenstruck/Bytes
$disk->total(); // Zenstruck/Bytes
$disk->percentUsed(); // Percent

$memory = $system->memory(); // StorageInfo
$memory->free(); // Zenstruck/Bytes
$memory->used(); // Zenstruck/Bytes
$memory->total(); // Zenstruck/Bytes
$memory->percentUsed(); // Percent

$php = $system->php(); // PhpInfo
(string) $php; // output of phpinfo()
$php->version(); // PhpVersionInfo
$php->symfonyVersion(); // SymfonyVersionInfo

$opcache = $php->opcache(); // OpCacheInfo
$opcache->memory(); // StorageInfo
$opcache->hits(); // int
$opcache->misses(); // int
$opcache->hitRate(); // Percent

$apcu = $php->apcu(); // ApcuInfo
$apcu->memory(); // StorageInfo
$apcu->hits(); // int
$apcu->misses(); // int
$apcu->hitRate(); // Percent
$apcu->percentFragmented(); // Percent
```

## Integrations

### [OhDear Application Monitoring](https://ohdear.app)

A base controller is provided to integrate with OhDear's
[application monitoring](https://ohdear.app/docs/features/application-health-monitoring) feature.

First, create a controller that extends `Liip\Monitor\Controller\OhDearController` with
the desired route:

```php
namespace App\Controller;

use Liip\Monitor\Controller\OhDearController as BaseOhDearController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route('/health-check')]
class OhDearController extends BaseOhDearController
{
}
```

Now, enable application monitoring in OhDear and add the expected Full URL
(ie `https://myapp.com/health-check`) to the _Health Report URL_.

On the settings page, note the _Health Report Secret_ (or generate one). Add this value to
a `OH_DEAR_MONITOR_SECRET` environment variable in your application.

That's it! Once you deploy to production, OhDear will start receiving health reports for all
your checks.

> [!NOTE]
> If you wish to restrict the check suites that are reported to OhDear, override the
> `OhDearController::checks()` in your controller.

## Full Default Configuration

```yaml
liip_monitor:

    # Default TTL for checks
    default_ttl:          null
    logging:
        enabled:              false
    mailer:
        enabled:              false
        recipient:            [] # Required
        sender:               null
        subject:              'Health Check Failed'
        send_on_warning:      false
        send_on_skip:         false
        send_on_unknown:      false
    checks:

        # fails/warns if system memory usage % is above thresholds
        system_memory_usage:
            enabled:              false
            warning:              70%
            critical:             90%
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails/warns if disk usage % is above thresholds
        system_disk_usage:

            # Prototype
            -
                path:                 ~ # Required
                warning:              70%
                critical:             90%
                suite:                []
                ttl:                  null
                label:                null
                id:                   null

        # fails/warns if disk free space is below thresholds
        system_free_disk_space:

            # Prototype
            -
                path:                 ~ # Required
                warning:              ~ # Required, Example: 20GB
                critical:             ~ # Required, Example: 5GB
                suite:                []
                ttl:                  null
                label:                null
                id:                   null

        # warns if system reboot is required
        system_reboot:
            enabled:              false
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails/warns if 15-minute load average is above thresholds
        system_load_average:
            1_minute:
                enabled:              false
                warning:              70%
                critical:             90%
                suite:                []
                ttl:                  null
                label:                null
                id:                   null
            5_minute:
                enabled:              false
                warning:              70%
                critical:             90%
                suite:                []
                ttl:                  null
                label:                null
                id:                   null
            15_minute:
                enabled:              false
                warning:              70%
                critical:             90%
                suite:                []
                ttl:                  null
                label:                null
                id:                   null

        # fails/warns if apcu memory usage % is above thresholds
        apcu_memory_usage:
            enabled:              false
            warning:              70%
            critical:             90%
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails/warns if apcu fragmentation % is above thresholds
        apcu_fragmentation:
            enabled:              false
            warning:              70%
            critical:             90%
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails/warns if opcache memory usage % is above thresholds
        opcache_memory_usage:
            enabled:              false
            warning:              70%
            critical:             90%
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails if EOL, warns if patch update required
        php_version:
            enabled:              false
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails if vulnerabilities found
        composer_audit:
            enabled:              false
            path:                 '%kernel.project_dir%'
            binary:               null
            suite:                []
            ttl:                  null
            label:                null
            id:                   null

        # fails if EOL, warns if patch update required
        symfony_version:
            enabled:              false
            suite:                []
            ttl:                  null
            label:                null
            id:                   null
        ping_url:

            # Prototype
            -
                url:                  ~ # Required
                method:               GET

                # See HttpClientInterface::DEFAULT_OPTIONS
                options:              []

                # Leave null to ensure "successful" (2xx) status code
                expected_status_code: null
                expected_content:     null

                # Milliseconds
                warning_duration:     null

                # Milliseconds
                critical_duration:    null
                suite:                []
                ttl:                  null
                label:                null
                id:                   null

        # fails if dbal connection fails
        dbal_connection:

            # Prototype
            name:
                suite:                []
                ttl:                  null
                label:                null
                id:                   null
```
