var Health = Em.Application.create();

Health.Check = Em.Object.extend({
    checkName: null,
    message: null,
    status: false,
    id: null,

    failed: function() {
        return !this.status;
    }.property('status'),

    runUrl: function() {
        return api.run_single_check.replace('replaceme', this.checkName);
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
    }
});

Health.itemRowView = Ember.View.extend({
});

Health.ResultView = Ember.View.create({
    templateName: 'result-template'
});

Health.ResultView.appendTo('#container');

Health.healthController.runChecks();
