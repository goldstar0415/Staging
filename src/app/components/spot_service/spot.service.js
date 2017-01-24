(function () {
  'use strict';

  /*
   * Service for spot management
   */
  angular
    .module('zoomtivity')
    .factory('SpotService', SpotService);

  /** @ngInject */
  function SpotService(Spot, moment, toastr, dialogs, $rootScope, SignUpService, DATE_FORMAT) {
    var commentIndex;
    var $scope;

    return {
      setScope: setScope,
      removeSpot: removeSpot,
      formatSpot: formatSpot,
      //mapNextPhoto: mapNextPhoto,
      //mapPrevPhoto: mapPrevPhoto,
      //mapNextReview: mapNextReview,
      //mapPrevReview: mapPrevReview,
      initMarker: initMarker,
      saveToCalendar: saveToCalendar,
      removeFromCalendar: removeFromCalendar,
      addToFavorite: addToFavorite,
      removeFromFavorite: removeFromFavorite
    };

    /*
     * Add spot to calendar
     * @param spot {Spot}
     */
    function saveToCalendar(spot) {
      if (checkUser()) {
        Spot.saveToCalendar({id: spot.id}, function () {
          spot.is_saved = true;
          syncSpots(spot.id, {is_saved: true});
        });
      }
    }

    /*
     * Delete spot from calendar
     * @param spot {Spot}
     */
    function removeFromCalendar(spot) {
      Spot.removeFromCalendar({id: spot.id}, function () {
        spot.is_saved = false;
        syncSpots(spot.id, {is_saved: false});
      });
    }

    /*
     * Add spot to favorites
     * @param spot {Spot}
     */
    function addToFavorite(spot) {
      if (checkUser()) {
        Spot.favorite({id: spot.id}, function () {
          spot.is_favorite = true;
          syncSpots(spot.id, {is_favorite: true});
        });
      }
    }

    /*
     * Remove spot from favorites
     * @param spot {Spot}
     * @param callback {Function}
     */
    function removeFromFavorite(spot, callback) {
      Spot.unfavorite({id: spot.id}, function () {
        spot.is_favorite = false;
        syncSpots(spot.id, {is_favorite: false});

        if (callback) {
          callback();
        }
      });
    }

    /*
     * Remove spot
     * @param spot {Spot}
     * @param idx {number} spot index
     * @param callback {Function}
     */
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

    /*
     * Convert spot dates to normal format
     * @param spot {Spot}
     */
    function formatSpot(spots) {
      if (spots instanceof Array) {
        return _.each(spots, function (spot) {
          _formatSpot(spot);
        });
      } else {
        return _formatSpot(spots);
      }
    }

    function _formatSpot(spot) {
      spot.type = spot.category.type.display_name;
      if (spot.start_date && spot.end_date) {
        spot.start_time = moment(spot.start_date).format(DATE_FORMAT.time);
        spot.end_time = moment(spot.end_date).format(DATE_FORMAT.time);
        spot.start_date = moment(spot.start_date).format('YYYY-MM-DD');
        spot.end_date = moment(spot.end_date).format('YYYY-MM-DD');
      }
      // fix URLs
      ['restaurant', 'hotel'].forEach(function(t) {
        ['google_url', 'facebook_url', 'instagram_url', 'tumbler_url', 'twitter_url', 'vk_url'].forEach(function(n) {
          if (spot[t] && spot[t][n]) {
            spot[t][n] = prefixUrl(spot[t][n]);
          }
        });
      });
      var validWebsites = _.filter(spot.web_sites, function(ws){ return _.isString(ws) && ws.trim().length > 0; });
      spot.web_sites = validWebsites.length > 0 ? validWebsites : null;
      if (_.isArray(spot.web_sites) && spot.web_sites.length > 0) {
        spot.web_sites.forEach(function(ws, i){
          spot.web_sites[i] = prefixUrl(ws);
        });
      }

      return spot;
    }

    function prefixUrl(url, https) {
      return /:\/\//i.test(url) ? url : ((https ? 'https' : 'http') + url);
    }

    //open sign up modal if user not authorized
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
        if (spot) {
          spot[key] = data[key];
        }
      }
    }

    function initMarker(spot) {
      $scope.data.spot = spot;

    }

    function setScope(s) {
      $scope = s;
    }
  }
})();
