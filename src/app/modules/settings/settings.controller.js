(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SettingsController', SettingsController);

  /** @ngInject */
  function SettingsController($scope, $rootScope, UploaderService, currentUser, User, DATE_FORMAT, CropService, toastr, moment, $http, API_URL) {
    var vm = this;
    vm.endDate = moment().toDate();
    vm.data = currentUser;
    vm.data.social_facebook = isSocial('facebook');
    vm.data.social_google = isSocial('google');
    vm.addSocial = addSocial;
    vm.removeSocial = removeSocial;
    vm.images = UploaderService.images;
    vm.minDate = '01.01.1940';

    vm.privacyOptions = [
      {value: 1, label: 'All users have access'},
      {value: 2, label: 'Only followers&following have access'},
      {value: 3, label: 'Only following has access'},
      {value: 4, label: 'Only authorized users have access'},
      {value: 5, label: 'Nobody has access'}
    ];
    vm.notificationOptions = [
      {value: true, label: 'Receive'},
      {value: false, label: 'Don\'t receive'}
    ];

    vm.savePersonalSettings = function (form) {
      if (form.$valid) {
        $http.put(API_URL + '/settings', {
          type: 'personal',
          params: {
            first_name: vm.data.first_name,
            last_name: vm.data.last_name,
            birth_date: vm.data.birth_date ? moment(vm.data.birth_date, 'MM.DD.YYYY').format('YYYY-MM-DD') : null,
            sex: vm.data.sex || '',
            //time_zone: vm.data.time_zone,
            description: vm.data.description,
            address: vm.data.address,
            location: vm.data.location
          }
        })
          .success(function (data, status, headers, config) {
            toastr.success('Settings saved');
          })
          .error(function (data, status, headers, config) {
            toastr.error('Incorrect input ');
          });
      }
    };
    vm.saveSecuritySettings = function (form) {
      if (form.$valid) {
        //send email
        $http
          .put(API_URL + '/settings', {
            type: 'security',
            params: {
              email: vm.data.newEmail
            }
          })
          .success(function (data, status, headers, config) {
            toastr.success('Settings saved');
            vm.data.email = vm.data.newEmail;
            vm.data.newEmail = '';
            form.$submitted = form.$touched = false;
          })
          .error(function (data, status, headers, config) {
            toastr.error('This email has already been taken')
          });
      } else {
        toastr.error('Email is not valid');
      }
    };
    vm.savePasswordSettings = function (form) {
      if (form.$valid) {
        //send pass settings
        $http.put(API_URL + '/settings', {
          type: 'password',
          params: {
            current_password: vm.data.currentPassword,
            password: vm.data.newPassword,
            password_confirmation: vm.data.newPasswordConfirm
          }
        })
          .success(function (data, status, headers, config) {
            toastr.success('Settings saved')
          })
          .error(function (data, status, headers, config) {
            toastr.error('Incorrect input ')
          });
      } else {
        toastr.error('Incorrect input');
      }
    };
    vm.savePrivacySettings = function () {
      $http.put(API_URL + '/settings', {
        type: 'privacy',
        params: {
          privacy_events: vm.data.privacy_events,
          privacy_favorites: vm.data.privacy_favorites,
          privacy_followers: vm.data.privacy_followers,
          privacy_followings: vm.data.privacy_followings,
          privacy_wall: vm.data.privacy_wall,
          privacy_info: vm.data.privacy_info,
          privacy_photo_map: vm.data.privacy_photo_map
        }
      })
        .success(function (data, status, headers, config) {
          toastr.success('Settings saved')
        })
        .error(function (data, status, headers, config) {
          toastr.error('Incorrect input ')
        });
    };
    vm.saveNotificationSettings = function () {
      $http
        .put(API_URL + '/settings', {
          type: 'notifications',
          params: {
            notification_letter: vm.data.notification_letter,
            notification_wall_post: vm.data.notification_wall_post,
            notification_follow: vm.data.notification_follow,
            notification_new_spot: vm.data.notification_new_spot,
            notification_coming_spot: vm.data.notification_coming_spot
          }
        })
        .success(function (data, status, headers, config) {
          toastr.success('Settings saved')
        })
        .error(function (data, status, headers, config) {
          toastr.error('Incorrect input ')
        });
    };

    function addSocial(type) {
      if (!isSocial(type)) {
        window.location.href = API_URL + '/account/' + type;
      }
    }

    function removeSocial(type) {
      if (isSocial(type)) {
        var url = API_URL + '/account/' + type;
        $http
          .delete(url)
          .success(function (data, status, headers, config) {
            toastr.success('Settings saved')
          })
          .error(function (data, status, headers, config) {
            toastr.error('Something went wrong')
          });
      }
    }

    function isSocial(type) {
      return currentUser.attached_socials.indexOf(type) >= 0 ? 1 : 0;
    }

    //change avatar
    $scope.$watch('Settings.images.files', function (val, test) {
      if (vm.images.files.length > 0) {
        CropService.crop(vm.images.files[0], 512, 512, function (result) {
          vm.images.files.splice(0, vm.images.files.length);
          if (result) {
            User.setAvatar({}, {avatar: result},
              function (user) {
                $rootScope.currentUser.avatar_url = user.avatar_url;
                vm.data.avatar_url = user.avatar_url;
                toastr.success('Avatar changed');
              });
          }
        });
      }
    });
  }
})();
