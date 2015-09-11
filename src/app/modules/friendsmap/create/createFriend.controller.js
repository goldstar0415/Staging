(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CreateFriendController', CreateFriendController);

  /** @ngInject */
  function CreateFriendController(MapService, friend, toastr, $state, Friends, DATE_FORMAT) {
    var vm = this;
    vm.endDate = moment().format(DATE_FORMAT.datepicker.date);

    vm.friend = friend;
    vm.edit = $state.current.edit;
    var params = {
      first_name: vm.friend.first_name,
      last_name: vm.friend.first_name,
      birth_date: vm.friend.first_name,
      phone: vm.friend.first_name,
      email: vm.friend.first_name,
      location: vm.friend.first_name,
      address: vm.friend.first_name,
      note: vm.friend.first_name
    };

    vm.SaveFriend = function (form) {
      var friend = angular.copy(vm.friend);
      friend.birth_date = friend.birth_date ? moment(friend.birth_date).format('YYYY-MM-DD') : null;

      if (form.$valid) {
        if (vm.edit) {
          friend.$update()
            .then(function () {
              $state.go('friendsmap');
            })
            .catch(function () {
              toastr.error('Invalid input');
            });
        } else {
          friend.$save()
            .then(function () {
              $state.go('friendsmap');
            })
            .catch(function () {
              toastr.error('Invalid input');
            })
        }
      } else {
        toastr.error('Invalid input');
      }
    };

    var map = MapService.GetMap();
    map.on('click', function (e) {
      onMapClick(e);
    });
    function onMapClick(event) {
      MapService.GetAddressByLatlng(event.latlng, function (data) {
        vm.friend.location = event.latlng;
        vm.friend.address = data.display_name;
      });
    }
  }

})();
