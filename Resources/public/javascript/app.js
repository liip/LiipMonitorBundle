var Health = Em.Application.create( {
    ready: function() {
        Health.ResultView.appendTo('#container');
        Health.healthController.runChecks();
    }
});

Health.Check = Em.Object.extend({
    checkName: null,
    message: null,
    status: false,
    service_id: null,

    icon: function() {
        if (this.status_name === "check_result_ok") {
            return "icon-ok-sign";
        }

        if (this.status_name === "check_result_warning") {
            return "icon-warning-sign";
        }

        if (this.status_name === "check_result_critical") {
            return "icon-fire";
        }

        if (this.status_name === "check_result_unknown") {
            return "icon-question-sign";
        }

        return "icon-exclamation-sign";
    }.property('status_name'),

    runUrl: function() {
        return api.liip_monitor_run_single_check.replace('replaceme', this.service_id);
    }.property('checkName')
});

Health.healthController = Em.ArrayProxy.create({
    batchCheck: function() {
        var self = this;
        jQuery.ajax({
            url: api.liip_monitor_run_all_checks,
            type: 'POST',
            dataType: 'json',
            success: function(data) {
                let checks = data.checks.map(function (item) {
                    return Health.Check.create(item);
                });
                self.set('content', checks);
            },
            error: function() {
                console.log("error while loading health checks");
            }
        });
    },

    streamCheck: function() {
        let self = this;
        let lastResponseLength = false;
        jQuery.ajax({
            url: api.liip_monitor_stream_all_checks,
            type: 'POST',
            xhrFields: {
                onprogress: function(e)
                {
                    let progressResponse;
                    let response = e.currentTarget.response;

                    progressResponse = lastResponseLength === false ? response : response.substring(lastResponseLength);

                    lastResponseLength = response.length;

                    progressResponse = progressResponse.match(/{"checkName"(.*?)}/)[0];
                    Health.healthController.addObject(JSON.parse(progressResponse));

                }
            },
            error: function() {
                console.log("error while loading health checks");
            }
        });
    },

    content: [],

    runChecks: function () {
        if (is_lazy_run) {
            return this.streamCheck()
        }
        return this.batchCheck()

    },

    repeatCheck: function(check) {
        var self = this;
        $.ajax({
           url: check.get('runUrl'),
           type: 'POST',
           dataType: 'json',
           success: function(data) {
               var updatedCheck = Health.Check.create(data);
               var idx = self._findItemIndex(check);
               if (idx > -1) {
                   Health.healthController.replaceContent(self._findItemIndex(check), 1, [updatedCheck]);
               } else {
                   console.log('item is not part of the content collection');
               }
           },
           error: function() {
               console.log("error while running health check");
           }
        });
    },

    _findItemIndex: function(check) {
        var len = this.content.get('length');
        var last = null, next, found = false, ret = -1;

        for(var idx=0; idx<len && !found; idx++) {
            next = this.nextObject(idx, last);
            if (next.get('service_id') == check.get('service_id')) {
                found = true;
                ret = idx;
            }
            last = next;
        }
        next = null;
        return ret;
    }
});

Health.itemRowView = Ember.View.extend({
    repeatCheck: function(event, view, context) {
        Health.healthController.repeatCheck(context.get('content'));
    }
});

Health.ResultView = Ember.View.create({
    templateName: 'result-template'
});
