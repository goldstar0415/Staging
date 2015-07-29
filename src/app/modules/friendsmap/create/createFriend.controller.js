(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CreateFriendController', CreateFriendController);

  /** @ngInject */
  function CreateFriendController(MapService, friend, toastr, $state) {
    var vm = this;
    vm.friend = friend;
    vm.edit = $state.current.edit;

    vm.SaveFriend = function(form) {
      if(form.$valid) {
        if(vm.edit) {
          vm.friend.$update()
            .then(function() {
              $state.go('friendsmap');
            })
            .catch(function() {
              toastr.error('Invalid input');
            });
        } else {
          vm.friend.$save()
            .then(function() {
              $state.go('friendsmap');
            })
            .catch(function() {
              toastr.error('Invalid input');
            })
        }
      } else {
        toastr.error('Invalid input');
      }
    };

    var map = MapService.GetMap();
    map.on('click', function(e) {
      onMapClick(e);
    });
    function onMapClick(event) {
      MapService.GetAddressByLatlng(event.latlng, function(data) {
        vm.friend.location = event.latlng;
        vm.friend.address = data.display_name;
      });
    }
  }

})();
