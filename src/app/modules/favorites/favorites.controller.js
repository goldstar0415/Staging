(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FavoritesController', FavoritesController);

  /** @ngInject */
  function FavoritesController(favorites) {
    var vm = this;
    vm.spots = favorites;

    vm.saveToCalendar = function (spot) {
      spot.is_saved = true;
      spot.$saveToCalendar(function () {
      });
    };

    vm.removeFromCalendar = function (spot) {
      spot.is_saved = false;
      spot.$removeFromCalendar(function () {
      });
    };

    vm.addToFavorite = function (spot) {
      spot.is_favorite = true;
      spot.$favorite(function () {
      });
    };

    vm.removeFromFavorite = function (spot) {
      spot.is_favorite = false;
      spot.$unfavorite(function () {
      });
    };
  }
})();
