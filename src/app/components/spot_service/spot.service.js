(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('SpotService', SpotService);

  /** @ngInject */
  function SpotService(Spot, moment, toastr, dialogs) {
    return {
      removeSpot: removeSpot,
      formatSpot: formatSpot,
      saveToCalendar: saveToCalendar,
      removeFromCalendar: removeFromCalendar,
      addToFavorite: addToFavorite,
      removeFromFavorite: removeFromFavorite
    };

    function saveToCalendar(spot) {
      Spot.saveToCalendar({id: spot.id}, function () {
        spot.is_saved = true;
      });
    }

    function removeFromCalendar(spot) {
      Spot.removeFromCalendar({id: spot.id}, function () {
        spot.is_saved = false;
      });
    }

    function addToFavorite(spot) {
      Spot.favorite({id: spot.id}, function () {
        spot.is_favorite = true;
      });
    }

    function removeFromFavorite(spot, callback) {
      Spot.unfavorite({id: spot.id}, function() {
        spot.is_favorite = false;
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
  }
})();
