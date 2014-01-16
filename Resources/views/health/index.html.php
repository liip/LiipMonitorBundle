<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Health Check</title>
    <?php echo $css; ?>
    <?php echo $urls; ?>
</head>
<body>
<div id="container">
    <h1>System Health Status</h1>
    <script type="text/x-handlebars" data-template-name="result-template">
        {{#if Health.healthController.content.length}}
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
                        {{#view Health.itemRowView contentBinding="this" tagName="tr" classBinding="content.status_name"}}
                            <td>
                                <i {{bindAttr class="content.icon"}}></i>&nbsp;&nbsp;{{content.checkName}}
                            </td>
                            <td>{{content.message}}</td>
                            <td><button class=".btn" {{action "repeatCheck"}}>GO</button></td>
                        {{/view}}
                    {{/each}}
                </tbody>
            </table>
        {{else}}
            <div>
                <h4>No Health Checks Registered</h4>
                <p>
                    To register health checks you need to tag your services in the service container definition by using the following tag: <code>liip_monitor.check</code>.
                </p>
            </div>
        {{/if}}
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
        <dt><a href="<?php echo $request->getUriForPath($request->getPathInfo()) ?>"><?php echo $request->getPathInfo() ?></a></dt>
        <dl>
            Returns this HTML view. If the check was performed without errors then the table row will be <strong>green</strong>, else it will be shown as <strong>red</strong>.
        </dl>
        <dt><a href="<?php echo $request->getUriForPath($request->getPathInfo().'checks') ?>"><?php echo $request->getPathInfo().'checks' ?></a></dt>
        <dd>
            Returns a list of available health checks as a JSON array.
            <pre>
$ curl -XPOST -H "Accept: application/json" <?php echo $request->getUriForPath($request->getPathInfo().'checks') ?>

[
    "monitor.check.jackrabbit",
    "monitor.check.redis",
    "monitor.check.memcache",
    "monitor.check.php_extensions"
]</pre>
        </dd>

        <dt><a href="<?php echo $request->getUriForPath($request->getPathInfo().'run') ?>"><?php echo $request->getPathInfo().'run' ?></a></dt>
        <dd>Performs all health checks and returns the results as an array of JSON objects.
<pre>
$ curl -XPOST -H "Accept: application/json" <?php echo $request->getUriForPath($request->getPathInfo().'run') ?>

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

        <dt><?php echo $request->getPathInfo().'run/check_id' ?></dt>
        <dd>Runs the health check specified by <code>check_id</code> and returns the result as a JSON object.
<pre>
$ curl -XPOST -H "Accept: application/json" <?php echo $request->getUriForPath($request->getPathInfo().'checks/monitor.check.redis') ?>

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
<?php echo $javascript; ?>
</body>
</html>
