(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('contactsImport', ContactsImportDirective);

  function ContactsImportDirective() {
    return {
      restrict: 'EA',
      template: '<div></div>',
      controller: ContactsImportDirectiveController,
      controllerAs: 'modal',
      bindToController: true,
      scope: {

      }
    };
  }

  function ContactsImportModalController(users, API_URL, User, $modalInstance, $rootScope) {
    var _this = this;
    this.API_URL = API_URL;
    console.log('modal import contacts', users);
    User.importInfo({emails: _.pluck(users, 'email')}).$promise
      .then(function(userInfos) {
        userInfos.forEach(function(u) {
          var _u = _.findWhere(users, {email: u.email});
          if (_u) {
            _u.$exists   = u.exists;
            _u.$friends  = u.friends;
            console.log(u);
            if (u.id) {
              _u.id = u.id;
            }
          }
        });
      })
      .finally(function () {
        _this.users = users;
        console.log(_this.users);
      });

    this.follow = function (user) {
      console.log(user);
      if (user && user.id) {
        user.$disabled = true;
        User.follow({user_id: user.id}).$promise
          .then(function () {
            user.$success = true;
            toastr.success('User has been followed by you', 'Friends Import');
            setTimeout(function() {
              $rootScope.$broadcast('friendsMap.refresh.friends.list');
            }, 1);
          }, function (e) {
            toastr.error(e);
          });
      }
    };

    this.invite = function (user) {
      if (user && user.email) {
        user.$disabled = true;
        User.inviteEmail({email: user.email}).$promise
          .then(function () {
            user.$success = true;
            toastr.success('User ' + user.email + ' has been invited to Zoomtivity by you', 'Friends Import');
          }, function (e) {
            toastr.error(e);
          });
      }
    };

    this.close = function () {
      $modalInstance.close();
    };
  }

  /** @ngInject */
  function ContactsImportDirectiveController($modal, $rootScope) {
    var vm = this;
    //vm.API_URL = API_URL;
    //vm.users = contacts;

    vm.openDialog = function (users) {
      $modal.open({
        templateUrl: '/app/components/contacts_import/contacts_import.html',
        controller: ContactsImportModalController,
        controllerAs: 'modal',
        resolve: {
          users: function() {
            return users;
          }
        }
      });
    };

      var callbackKey = 'ContactsImportDirective-openDialog';
      $rootScope.clearEventListenerCallbacks = $rootScope.clearEventListenerCallbacks || {};
      if (callbackKey in $rootScope.clearEventListenerCallbacks) {
          // don't want to have multiple callbacks per one event call. clear previous handler
          $rootScope.clearEventListenerCallbacks[callbackKey]();
      }
      $rootScope.clearEventListenerCallbacks[callbackKey] =
          $rootScope.$on('contacts.import.after', function(event, friends) {
              vm.openDialog(friends);
          });
  }
})();
