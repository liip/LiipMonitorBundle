<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Health Check</title>
    <link rel="stylesheet" href="/bundles/liipmonitor/css/style.css" type="text/css">
    <script type="text/javascript" charset="utf-8">
        api = {
            run_all_checks: "<?php echo $view['router']->generate('run_all_checks'); ?>",
            run_single_check: "<?php echo $view['router']->generate('run_single_check', array('check_id' => 'replaceme')); ?>"
        };
    </script>
</head>
<body id="container">
    <h1>System Health Status</h1>
    <script type="text/x-handlebars" data-template-name="result-template">
    <table class="test-result">
        <thead>
        <tr>
            <th>Name</th>
            <th>Message</th>
            <th>Re-Run</th>
        </tr>
        </thead>
        <tbody>
            {{#each Health.healthController.content}}
                {{#view Health.itemRowView contentBinding="this" tagName="tr" classBinding="content.failed"}}
                    <td>{{content.checkName}}</td>
                    <td>{{content.message}}</td>
                    <td><a href="#" {{action "reRunCheck"}}>GO</a></td>
                {{/view}}
            {{/each}}
        </tbody>
    </table>
    </script>
    <script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/jquery-1.7.1.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/ember-0.9.5.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/app.js"></script>
</body>
</html>