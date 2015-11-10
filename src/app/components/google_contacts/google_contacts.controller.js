(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('GoogleContactsController', GoogleContactsController);

  /** @ngInject */
  function GoogleContactsController(contacts, friends, $modalInstance, API_URL, Friends) {
    var vm = this;
    vm.API_URL = API_URL;
    vm.users = contacts;

    vm.save = function () {
      $modalInstance.close();
      _.each(vm.users, function (user) {
        if (user.selected) {
          var photo = user.photo,
            user_name = (user.first_name || user.last_name || user.email || user.phone);
          Friends.save({
            first_name: user.first_name,
            last_name: user.last_name,
            email: user.email,
            phone: user.phone
          }, function (friend) {
            if (photo) {
              convertToBase64(photo, function (data) {
                Friends.setAvatar({id: friend.id}, {avatar: data}, function (friendPhoto) {
                  friends.push(friendPhoto);
                });
              });
            } else {
              friends.push(friend);
            }

            toastr.success(user_name + ' successfully imported')
          }, function () {
            toastr.error(user_name + ' import failed')
          });
        }
      });
    };

    function convertToBase64(url, callback, outputFormat) {
      var img = new Image();
      img.crossOrigin = 'Anonymous';
      img.onload = function(){
        var canvas = document.createElement('CANVAS');
        var ctx = canvas.getContext('2d');
        var dataURL;
        canvas.height = this.height;
        canvas.width = this.width;
        ctx.drawImage(this, 0, 0);
        dataURL = canvas.toDataURL(outputFormat);
        callback(dataURL);
        canvas = null;
      };
      img.src = url;
    }


    //close modal
    vm.close = function () {
      $modalInstance.close();
    };
  }
})();
