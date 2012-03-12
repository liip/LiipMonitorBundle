<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Health Check</title>
    <link rel="stylesheet" href="/bundles/liipmonitor/css/bootstrap/css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="/bundles/liipmonitor/css/style.css" type="text/css">
    <script type="text/javascript" charset="utf-8">
        api = {
            run_all_checks: "<?php echo $view['router']->generate('run_all_checks'); ?>",
            run_single_check: "<?php echo $view['router']->generate('run_single_check', array('check_id' => 'replaceme')); ?>"
        };
    </script>
</head>
<body>
<div id="container">
    <h1>System Health Status</h1>
    <script type="text/x-handlebars" data-template-name="result-template">
    <table class="table table-bordered table-condensed test-result">
        <thead>
        <tr>
            <th>Name</th>
            <th>Message</th>
            <th>Repeat</th>
        </tr>
        </thead>
        <tbody>
            {{#each Health.healthController.content}}
                {{#view Health.itemRowView contentBinding="this" tagName="tr" classBinding="content.failed"}}
                    <td>
                        {{#if content.failed}}
                        <i class="icon-fire"></i>
                        {{else}}
                        <i class="icon-ok"></i>
                        {{/if}}
                        &nbsp;&nbsp;{{content.checkName}}
                    </td>
                    <td>{{content.message}}</td>
                    <td><button class=".btn" {{action "repeatCheck"}}>GO</button></td>
                {{/view}}
            {{/each}}
        </tbody>
    </table>
    </script>
</div>
<div id="info">
    <h2>Documentation</h2>
    <p>This service performs user defined health checks for the various services that compose an application.</p>
    <p>It offers a REST API where you can list available health checks and also gives the chance to run them individually or all together.
        This page is just a HTML view of the JSON response provided by one of those API methods.
    </p>
    <h3>Repeating failed tests</h3>
    <p>
        The third column of the table displays a button labeled <strong>GO</strong>. By pressing this button you can re run a failed the tests to see if it came back to normal.
    </p>
    <h3>REST API</h3>
    <dl>
        <dt>/health</dt>
        <dl>
            Returns this HTML view. If the check was performed without errors then the table row will be <strong>green</strong>, else it will be shown as <strong>red</strong>.
        </dl>
        <dt>/health/checks</dt>
        <dd>
            Returns a list of available health checks as a JSON array.
            <pre>
$ curl http://api.nzz.lo/app_dev.php/health/checks
[
    "monitor.check.jackrabbit",
    "monitor.check.redis",
    "monitor.check.memcache",
    "monitor.check.php_extensions"
]</pre>
        </dd>
        <dt>/health/run</dt>
        <dd>Performs all health checks and returns the results as an array of JSON objects.
<pre>
$ curl http://api.nzz.lo/app_dev.php/health/run
{
"checks":
    [
        {"checkName": "Jackrabbit Health Check", "message": "OK", "status":true, "service_id": "monitor.check.jackrabbit"},
        {"checkName": "Redis Health Check", "message": "OK", "status":true, "service_id": "monitor.check.redis"},
        {"checkName": "Memcache Health Check", "message": "KO - No configuration set for session.save_path", "status":false, "service_id": "monitor.check.memcache"},
        {"checkName": "PHP Extensions Health Check", "message": "OK", "status":true, "service_id": "monitor.check.php_extensions"}
    ]
}</pre>
        </dd>
        <dt>/health/run/check_id</dt>
        <dd>Runs the health check specified by <code>check_id</code> and returns the result as a JSON object.
<pre>
$ curl http://api.nzz.lo/app_dev.php/health/run/monitor.check.redis
{
   "checkName": "Redis Health Check",
   "message": "OK",
   "status": true,
   "service_id": "monitor.check.redis"
}</pre>
        </dd>
        <dt>Check Result JSON Structure</dt>
        <dd>
            <dl>
                <dt><code>checkName</code></dt>
                <dd>A string providing the health check name.</dd>
                <dt><code>message</code></dt>
                <dd>A message returned by the health check. In case of an error the exception message is shown here.</dd>
                <dt><code>status</code></dt>
                <dd>A boolean indication if the health check passed.</dd>
                <dt><code>service_id</code></dt>
                <dd>The <code>service_id</code> specified in the service container configuration.</dd>
            </dl>
        </dd>
    </dl>
</div>
<script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/jquery-1.7.1.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/ember-0.9.5.min.js"></script>
<script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/app.js"></script>
</body>
</html>