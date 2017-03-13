(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('CreateFriendController', CreateFriendController);

  /** @ngInject */
  function CreateFriendController(MapService, friend, toastr, $state, Friends, DATE_FORMAT, UploaderService, CropService, API_URL, $timeout, $scope) {
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

    // image uploading
    var avatarName = null;
    var isChangedAvatar = false;
    vm.images = UploaderService.images;
    vm.maxPhotos = 1;
    vm.selectAvatar = false;
    vm.crop = {
      width: 600,
      height: 600
    };
    vm.firstload = true;
    vm.deletedImages = [];

    vm.init = function() {
      if (vm.edit) {
        vm.loadFriendAvatar();
        vm.saveCrop = true;
      }
      $scope.$watch('CreateFriend.images.files.length', function () {
        vm.checkFilesRestrictions();
      });
    };

    vm.SaveFriend = function (form) {
      if (form.$valid) {
        var request = angular.copy(vm.friend);
        request.birth_date = request.birth_date ? moment(request.birth_date, DATE_FORMAT.datepicker.date).format('YYYY-MM-DD') : null;

        if (vm.avatar && isChangedAvatar) {
          request.avatar = vm.avatar;
        }

        //console.log(request);

        var url = API_URL + '/friends';
        var req = {};
        req.payload = JSON.stringify(request);

        if (vm.edit) {
          req._method = 'PUT';
          url = API_URL + '/friends/' + friend.id;
        }
        vm.images.files = rejectOldFiles();
        vm.loading = true;

        UploaderService
            .upload(url, req)
            .then(function (resp) {
              $state.go('friendsmap');
            })
            .catch(function (resp) {
              vm.loading = false;
              console.log('Error', resp);

              if (resp.status == 413) {
                toastr.error('Images too large');
              } else {
                toastr.error('Save error');
              }
            });
      } else {
        toastr.error('Invalid input');
      }
    };

    vm.checkFilesRestrictions = function () {
      if (vm.images.files.length > 1) {
        toastr.error('You can\'t add more than one photo');
        var l = vm.images.files.length - 1;
        vm.images.files.splice(1, l);
      }
      if (vm.images.files.length > 0 && !vm.avatar) {
        _setAvatar(vm.images.files[0]);
      }
    };

    vm.changeAvatar = function (image) {
      if (vm.selectAvatar) {
        angular.element('#avatar_cancel_button').addClass('ng-hide');

        if (image.photo_url) {
          _setAvatar(image.photo_url.original, image.id);
        } else {
          _setAvatar(image);
        }

        vm.selectAvatar = false;
        vm.saveCrop = false;
      }
    };

    vm.saveAvatar = function () {
      vm.saveCrop = true;
      isChangedAvatar = true;
    };

    vm.deleteImage = function (idx, id) {
      if (id) {
        if (vm.images.files[idx].id == avatarName) {
          vm.cropAvatar = '';
          vm.avatar = null;
        }

        vm.deletedImages.push(id);
        vm.images.files.splice(idx, 1);
      } else {
        if (vm.images.files[idx].name == avatarName) {
          vm.cropAvatar = '';
          vm.avatar = null;
        }
        vm.images.files.splice(idx, 1);
      }
    };

    vm.loadFriendAvatar = function () {
      if ( vm.friend && vm.friend.avatar_url && vm.friend.avatar_url.original ) {
        vm.avatar = vm.friend.avatar_url.original;
        _setAvatar(vm.avatar, vm.friend.id);
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

    function rejectOldFiles() {
      return _.reject(vm.images.files, function (item) {
        return item.id ? true : false;
      })
    }

    function _setAvatar(image, id) {

      $timeout(function() {
        if (typeof image === 'string') {
          vm.cropAvatar = image;
          avatarName = id;
        } else if (image.photo_url) {
          vm.cropAvatar = image.photo_url.original;
          avatarName = image.id;
        } else {
          var reader = new FileReader();
          reader.onloadend = function () {
            vm.cropAvatar = reader.result;
            $scope.$apply();
          };
          avatarName = image.name;
          reader.readAsDataURL(image);
        }
      });
    }
  }

})();
