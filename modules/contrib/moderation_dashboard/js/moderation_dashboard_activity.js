(function ($, Drupal) {
  'use strict';

  Drupal.behaviors.moderationDashboardActivity = {
    attach: function (context, settings) {
      var $activity = $('.moderation-dashboard-activity', context).once('moderation-dashboard-activity');

      /* global Chart:object */
      if ($activity.length && settings.moderation_dashboard_activity && Chart) {
        var $canvas = $('<canvas width="500" height="500"></canvas>');
        $activity.append($canvas);

        new Chart($canvas, {
          type: 'horizontalBar',
          data: settings.moderation_dashboard_activity,
          options: {
            scales: {
              xAxes: [{
                stacked: true,
                ticks: {
                  beginAtZero: true
                }
              }],
              yAxes: [{
                stacked: true,
                ticks: {
                  beginAtZero: true
                }
              }]
            }
          }
        });
      }
    }
  };

}(jQuery, Drupal));
