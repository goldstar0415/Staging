(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SpotService', SpotService);

  /** @ngInject */
  function SpotService(Spot, moment, toastr, dialogs, $rootScope, SignUpService) {
    return {
      removeSpot: removeSpot,
      formatSpot: formatSpot,
      saveToCalendar: saveToCalendar,
      removeFromCalendar: removeFromCalendar,
      addToFavorite: addToFavorite,
      removeFromFavorite: removeFromFavorite
    };

    function saveToCalendar(spot) {
      if (checkUser()) {
        Spot.saveToCalendar({id: spot.id}, function () {
          spot.is_saved = true;
          syncSpots(spot.id, {is_saved: true});
        });
      }
    }

    function removeFromCalendar(spot) {
      Spot.removeFromCalendar({id: spot.id}, function () {
        spot.is_saved = false;
        syncSpots(spot.id, {is_saved: false});
      });
    }

    function addToFavorite(spot) {
      if (checkUser()) {
        Spot.favorite({id: spot.id}, function () {
          spot.is_favorite = true;
          syncSpots(spot.id, {is_favorite: true});
        });
      }
    }

    function removeFromFavorite(spot, callback) {
      Spot.unfavorite({id: spot.id}, function () {
        spot.is_favorite = false;
        syncSpots(spot.id, {is_favorite: false});

        if (callback) {
          callback();
        }
      });
    }

    function removeSpot(spot, idx, callback) {
      dialogs.confirm('Confirmation', 'Are you sure you want to delete spot?').result.then(function () {
        Spot.delete({id: spot.id}, function () {
          toastr.info('Spot successfully deleted');
          if (callback) {
            callback();
          }
        });
      });
    }

    function formatSpot(spot) {
      var offset = moment().utcOffset();

      spot.type = spot.category.type.display_name;
      spot.start_time = moment(spot.start_date).format('hh:mm a');
      spot.end_time = moment(spot.end_date).format('hh:mm a');
      spot.start_date = moment(spot.start_date).format('YYYY-MM-DD');
      spot.end_date = moment(spot.end_date).format('YYYY-MM-DD');

      return spot;
    }

    function checkUser() {
      if (!$rootScope.currentUser) {
        SignUpService.openModal('SignUpModal.html');
        return false;
      }
      return true;
    }

    function syncSpots(id, data) {
      //sync spots under the map
      _sync($rootScope.syncSpots, id, data);

      //sync spots on map
      var spots = _.pluck($rootScope.syncMapSpots, 'spot');
      _sync({data: spots}, id, data);
    }

    function _sync(source, id, data) {
      if (source && source.data.length > 0) {
        var key = _.keys(data)[0];
        var spot = _.findWhere(source.data, {id: id});
        console.log(source, spot);
        if (spot) {
          spot[key] = data[key];
        }
      }
    }
  }
})();
