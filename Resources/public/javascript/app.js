var Health = Em.Application.create();

Health.Check = Em.Object.extend({
    checkName: null,
    message: null,
    status: false,
    service_id: null,

    failed: function() {
        return !this.status;
    }.property('status'),

    runUrl: function() {
        return api.run_single_check.replace('replaceme', this.service_id);
    }.property('checkName')
});

Health.healthController = Em.ArrayProxy.create({

    content: [],

    runChecks: function() {
        var self = this;
        $.ajax({
            url: api.run_all_checks,
            dataType: 'json',
            success: function(data) {
                var checks = data.checks.map(function(item) {
                    return Health.Check.create(item);
                });
                self.set('content', checks);
            },
            error: function() {
                console.log("error while loading health checks");
            }
        });
    },

    reRunCheck: function(check) {
        var self = this;
        $.ajax({
           url: check.get('runUrl'),
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
    reRunCheck: function(evt, view, context) {
        event.preventDefault();
        Health.healthController.reRunCheck(context.get('content'));
    }
});

Health.ResultView = Ember.View.create({
    templateName: 'result-template'
});

Health.ResultView.appendTo('#container');

Health.healthController.runChecks();