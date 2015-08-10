(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FavoritesController', FavoritesController);

  /** @ngInject */
  function FavoritesController(favorites) {
    var vm = this;
    vm.spots = favorites;

  }
})();
