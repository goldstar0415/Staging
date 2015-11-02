(function () {
  'use strict';

  /*
   * Spots modal
   */
  angular
    .module('zoomtivity')
    .directive('facebookEvents', FacebookEventsDirective);

  /** @ngInject */
  function FacebookEventsDirective() {
    return {
      restrict: 'EA',
      template: '<a ng-click="FB.import()" class="pull-right">Import from Facebook</a>',
      controller: FacebookEventsCtrl,
      controllerAs: 'FB',
      bindToController: true
    };


    /** @ngInject */
    function FacebookEventsCtrl($modal, DATE_FORMAT) {
      var vm = this;
      vm.import = importEvents;

      /////////

      function importEvents() {
        FB.getLoginStatus(function (response) {
          if (response.status === 'connected') {
            //var uid = response.authResponse.userID;
            //var accessToken = response.authResponse.accessToken;
            _getFacebookEvents();
          } else {
            FB.login(_getFacebookEvents, {
              scope: 'user_events'
            });
          }
        });
      }

      function _getFacebookEvents() {
        FB.api(
          "/me/events",
          function (response) {
            console.log(response);
            if (response && !response.error) {
              var events = [];
              _.each(response.data, function (event) {
                if (event.name && event.place && event.place.location && event.start_time) {
                  var start_date = moment(event.start_time).format(DATE_FORMAT.backend),
                    end_date = event.end_time ? moment(event.end_time).format(DATE_FORMAT.backend) : start_date;

                  events.push({
                    title: event.name,
                    description: event.description,
                    start_date: start_date,
                    end_date: end_date,
                    spot_type_category_id: 6,  //delete it. for testing
                    locations: [{
                      location: {
                        lat: event.place.location.latitude,
                        lng: event.place.location.longitude
                      },
                      address: event.place.name || event.place.street
                    }]
                  });
                }
              });

              if (events.length > 0) {
                console.log(events);
                openModal(events);
              } else {
                toastr.error('You have no events in Facebook');
              }
            } else {
              toastr.error('Facebook API error');
            }
          }
        );
      }

      function openModal(events) {
        $modal.open({
          templateUrl: '/app/components/facebook_events/facebook_events.html',
          controller: SpotsModalController,
          controllerAs: 'modal',
          resolve: {
            spots: function () {
              return events;
            }
          }
        });
      }
    }

    /** @ngInject */
    function SpotsModalController(spots, $modalInstance, API_URL, Spot) {
      var vm = this;
      vm.API_URL = API_URL;
      vm.spots = spots;
      vm.close = close;
      vm.addSpot = addSpot;

      //close modal
      function close(isSave) {
        if (isSave) {
          _.each(vm.spots, function (spot) {
            if (spot.selected) {
              delete spot.selected;
              Spot.save(spot, function () {
                toastr.success(spot.title + ' successfully imported')
              }, function () {
                toastr.error(spot.title + ' import failed')
              });
            }
          });
        }

        $modalInstance.close();
      }

      //mark as selected spot
      function addSpot(spot) {
        spot.selected = !spot.selected;
      }
    }
  }

})
();
