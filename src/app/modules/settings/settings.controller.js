(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SettingsController', SettingsController);

  /** @ngInject */
  function SettingsController($scope, $rootScope, UploaderService, currentUser, User, DATE_FORMAT, CropService, toastr, moment, $http, API_URL) {
    var vm = this;
    vm.data = currentUser;
    vm.data.social_facebook = isSocial('facebook');
    vm.data.social_google = isSocial('google');
    vm.addSocial = addSocial;
    vm.removeSocial = removeSocial;
    vm.images = UploaderService.images;
    vm.saveSocialNetworks = saveSocialNetworks;
    vm.minDate = '01.01.1920';
    vm.maxDate = moment().toDate();

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

    /*
     * Save personal settings only
     * @param form {ngForm}
     */
    vm.savePersonalSettings = function (form) {
      if (form.$valid) {
        $http.put(API_URL + '/settings', {
          type: 'personal',
          params: {
            first_name: vm.data.first_name,
            last_name: vm.data.last_name,
            alias: vm.data.alias,
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
            $rootScope.currentUser.alias = vm.data.alias;
          })
          .error(function (data, status, headers, config) {
            toastr.error('Incorrect input ');
          });
      }
    };

    vm.checkAlias = function () {
      vm.aliasErrorMessage = null;
      User.checkAlias({alias: vm.data.alias}).$promise.catch(function (resp) {
        vm.aliasErrorMessage = resp.data.alias[0];
      });
    };

    /*
     * Change email
     * @param form {ngForm}
     */
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
            vm.email_verification = true;
            form.$submitted = false;
            form.$touched = false;
          })
          .error(function (data, status, headers, config) {
            toastr.error('This email has already been taken')
          });
      } else {
        toastr.error('Email is not valid');
      }
    };

    /*
     * Change password
     * @param form {ngForm}
     */
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

    /*
     * Save privacy settings only
     * @param form {ngForm}
     */
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

    /*
     * Save notification settings only
     * @param form {ngForm}
     */
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

    /*
     * Attach social to account
     * @param name {name}  name of social
     */
    function addSocial(name) {
      if (!isSocial(name)) {
        window.location.href = API_URL + '/account/' + name;
      }
    }

    /*
     * Remove attached social
     * @param name {name}  name of social
     */
    function removeSocial(name) {
      if (isSocial(name)) {
        var url = API_URL + '/account/' + name;
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

    function isSocial(name) {
      return currentUser.attached_socials.indexOf(name) >= 0 ? 1 : 0;
    }

    function saveSocialNetworks(form) {
      console.log(form);
      if (!form.$valid) return;

      //delete empty
      vm.socials =  _.pick(vm.socials, _.identity);

      $http
        .put(API_URL + '/settings', {
          type: 'socials',
          params: vm.data.social_links
        })
        .success(function (data, status, headers, config) {
          toastr.success('Settings saved')
        })
        .error(function (data, status, headers, config) {
          toastr.error('Incorrect input ')
        });
    }

    //change avatar
    $scope.$watch('Settings.images.files', function (val, test) {
      if (vm.images.files.length > 0) {
        CropService.crop(vm.images.files[0], 512, 512, true, function (result) {
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
