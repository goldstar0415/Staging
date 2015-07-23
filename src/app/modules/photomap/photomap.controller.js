(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('PhotomapController', PhotomapController);

  /** @ngInject */
  function PhotomapController(albums) {
    var vm = this;
    vm.albums = albums;
    console.log(albums);
  }
})();
