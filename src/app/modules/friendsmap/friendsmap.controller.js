(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FriendsmapController', FriendsmapController);

  /** @ngInject */
  function FriendsmapController(friends) {
    var vm = this;
    vm.friends = friends;
    console.log(friends);

    for(var k in friends){

    }
  }
})();
