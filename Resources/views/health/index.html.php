<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
    "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Health Check</title>
    <link rel="stylesheet" href="/bundles/liipmonitor/css/style.css" type="text/css">
</head>
<body id="monitor-content">
<table class="test-result">
    <tr>
        <th>Name</th>
        <th>Message</th>
        <th>Re-Run</th>
    </tr>
    <script type="text/x-handlebars">
      {{#view App.MyView}}
        <h1>Hello world!</h1>
      {{/view}}
    </script>
<?php foreach ($results as $result): ?>
    <tr class="<?php echo $result[1]->getStatus() ? 'pass' : 'fail' ?>">
        <td><?php echo $result[0]; ?></td>
        <td><?php echo $result[1]->getMessage(); ?></td>
        <td class="run-button"><a href="<?php echo $view['router']->generate('health_check') ?>">run</a></td>
    </tr>
<?php endforeach; ?>
<table>
    <script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/jquery-1.6.1.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/ember-0.9.5.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="/bundles/liipmonitor/javascript/app.js"></script>
    <script type="text/javascript" charset="utf-8">
        $(document).ready(function(){
            $('a').bind('click',function(event){
                event.preventDefault();
                var that = this;
                $.get(this.href, {}, function(response){
                    that.html(response);
                })
            });
        });
    </script>
</body>
</html>