(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('AlbumController', AlbumController);

  /** @ngInject */
  function AlbumController(album, $state, $rootScope) {
    var vm = this;
    vm.data = album;
  }
})();
