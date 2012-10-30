#!/usr/bin/python

import nagiosplugin

import urllib2
import simplejson

"""

A simple check script for use with LiipMonitorBundle to monitor symfony apps.

Usage:

1. Place the script in your nagios plugin dir (or define a custom one)

2. Nagios config:
define command{
        command_name    check_symfony_health
        command_line    $USER1$/check_symfony2.py -w 0  -c 0 -u https://$HOSTNAME$
}

3. Restart nagios.

4. Profit.

5. Forgo profit as you just realized you are missing the nagiosplugin module.

To remedy the situation, do:
    pip install nagiosplugin


Author: Tarjei Huse, tarjei.huse@gmail.com

"""

class Symfony2Check(nagiosplugin.Check):
    name = "Symfony2 health check"
    version = "1.0"

    def __init__(self, optparser, logger):
        self.log = logger
        optparser.description = 'Health check of Symfony2 application'
        optparser.version = '1.0'
        optparser.add_option(
          '-w', '--warning', default='1', metavar='RANGE',
          help='warning threshold (default: %default%)')
        optparser.add_option(
          '-c', '--critical', default='1', metavar='RANGE',
          help='warning threshold (default: %default%)')
        optparser.add_option(
          '-u', '--url', help='Url to check')
        optparser.add_option(
          '-a', '--auth', help='Authentication', default=None)


    def process_args(self, options, args):
        self.warning = options.warning.rstrip('%')
        self.critical = options.critical.rstrip('%')
        if not options.url:
            raise Exception("Missing url option")
        self.url = options.url.strip()  + "/monitor/health/run"
        self.hostUrl = options.url.strip()
        if options.auth is not None:
            self.username, self.password = options.auth.split(":")
        else:
            self.username = None

    def obtain_data(self):
        self.badChecks = []
        try:
            content = self.fetch(self.url)
            json = simplejson.loads(content)

            if json['globalStatus'] is not 'OK':
                self.badChecks = []
                for check in json['checks']:
                    if check['status']:
                        self.badChecks.append(check["checkName"])

        except Exception, e:
            self.log.warn("Could not connect to url: " + self.url +" res:" + str(e))
            self.badChecks.append("config_error_in_nagios")

        self.measures = [nagiosplugin.Measure("Num_failed_checks", len(self.badChecks), warning=self.warning, critical=self.critical, minimum=0 )]


    def default_message(self):
        if len(self.badChecks):
            return "The following checks failed: %s" % (", ".join(self.badChecks))
        return "All checks pass."


    def fetch(self, url):
        if self.username is not None:
            passman = urllib2.HTTPPasswordMgrWithDefaultRealm()
            # this creates a password manager
            passman.add_password(None, url, self.username, self.password)
            authhandler = urllib2.HTTPBasicAuthHandler(passman)
            opener = urllib2.build_opener(authhandler)
            urllib2.install_opener(opener)
        handle = urllib2.urlopen(url)
        data = handle.read()
        return data


main = nagiosplugin.Controller(Symfony2Check)
if __name__ == '__main__':
   main()
