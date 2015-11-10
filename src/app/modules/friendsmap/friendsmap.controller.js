(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('FriendsmapController', FriendsmapController);

  /** @ngInject */
  function FriendsmapController(friends, MapService, Friends, CropService, $state, API_URL, $modal, GOOGLE_API_KEY, GOOGLE_CLIENT_ID) {
    var vm = this;
    var markers = [];
    vm.friends = format(friends);
    initMap();

    function format(friends) {
      return _.each(friends, function (friend) {
        friend.showCustom = true;
        friend.showSwitch = false;
        if (friend.location && friend.default_location) {
          if (friend.location.lat != friend.default_location.lat && friend.location.lng != friend.default_location.lng) {
            friend.showSwitch = true;
          }
        }
        friend.displayAddress = friend.address;
        friend.birth_date = moment(friend.birth_date).format('MM.DD.YYYY')
      })
    }

    function createMarker(iconUrl, title, location, user_id) {
      var icon = MapService.CreateCustomIcon(iconUrl, 'custom-map-icons');
      var options = {};

      if (icon) options.icon = icon;
      if (title) options.title = title;

      var marker = MapService.CreateMarker(location, options);
      if (user_id) {
        marker.on('click', function () {
          $state.go('profile.main', {user_id: user_id});
        });
      }

      return marker;
    }

    function initMap() {
      for (var k in friends) {
        var obj = friends[k];
        var title = obj.first_name + " " + obj.last_name;
        if (obj.location) {
          var m = createMarker(obj.avatar_url.thumb, title, obj.location, obj.friend_id);
          markers.push({marker: m, id: obj.id});
        }
      }

      if (friends.length > 1) {
        MapService.FitBoundsOfCurrentLayer();
      } else if (friends.length == 1) {
        if (friends[0].location) {
          MapService.GetMap().setView(friends[0].location, 10);
        }
      }
    }

    vm.switchLocation = function (item, custom) {
      if (item.showCustom != custom) {
        item.showCustom = custom;
        if (custom) {
          item.displayAddress = item.address;
          moveMarkerToLocation(item.id, item.location);
        } else {
          item.displayAddress = item.default_location.address;
          moveMarkerToLocation(item.id, item.default_location);
        }
      }

    };

    /*
     * Set avatar for friends
     * @param id {number} friend id
     * @param files {Array<File>}
     */
    vm.setAvatar = function (id, files) {
      if (files.length > 0) {
        CropService.crop(files[0], 512, 512, true, function (result) {
          if (result) {
            Friends.setAvatar({id: id}, {avatar: result}, function (res) {
              var marker = null;
              var friend = null;

              for (var k in markers) {
                if (markers[k].id == id) {
                  marker = markers[k].marker;
                }
              }

              for (var k in vm.friends) {
                if (vm.friends[k].id == id) {
                  friend = vm.friends[k]
                }
              }

              if (friend) {
                friend.avatar_url.medium = result;
              }

              if (marker) {
                var icon = MapService.CreateCustomIcon(result, 'custom-map-icons');
                marker.setIcon(icon);
              }
            });
          }
        });
      }
    };

    /*
     * Delete friend
     * @param id {number} friend id
     * @param idx {number} friend index
     */
    vm.removeFriend = function (id, idx) {
      Friends.deleteFriend({id: id}, function () {
        vm.friends.splice(idx, 1);

        for (var k in markers) {
          if (markers[k].id == id) {
            MapService.RemoveMarker(markers[k].marker);
          }
        }
      })
    };

    //callback from google contacts window
    window.modalContactsCallback = function (contacts) {
      console.log(contacts);

      $modal.open({
        templateUrl: '/app/components/google_contacts/google_contacts.html',
        controller: 'GoogleContactsController',
        controllerAs: 'modal',
        resolve: {
          contacts: function () {
            return contacts;
          },
          friends: function () {
            return vm.friends;
          }
        }
      });
    };

    vm.googleImport = function () {
      var width = angular.element(window).width() / 2,
        height = angular.element(window).height() / 1.5;
      openPopup(location.origin + '/api/google-contacts', "Google Contacts", width, height);

      //gapi.client.setApiKey(GOOGLE_API_KEY);
      //window.setTimeout(checkAuth,1);
    };

    function openPopup(url, title, w, h) {
      // Fixes dual-screen position
      var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
      var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;

      var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
      var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

      var left = ((width / 2) - (w / 2)) + dualScreenLeft;
      var top = ((height / 2) - (h / 2)) + dualScreenTop;
      var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

      // Puts focus on the newWindow
      if (window.focus) {
        newWindow.focus();
      }
    }

    function checkAuth() {
      gapi.auth.authorize({
        client_id: GOOGLE_CLIENT_ID,
        scope: 'https://www.googleapis.com/auth/plus.login',
        immediate: true
      }, handleAuthResult);
    }

    function handleAuthResult(authResult) {
      if (authResult && !authResult.error) {
        makeApiCall();
      } else {
        gapi.auth.authorize({
          client_id: GOOGLE_CLIENT_ID,
          scope: 'https://www.googleapis.com/auth/plus.login',
          immediate: false
        }, handleAuthResult);
      }
    }

    // Load the API and make an API call.  Display the results on the screen.
    function makeApiCall() {
      // Step 4: Load the Google+ API
      gapi.client.load('contacts', 'v1').then(function () {
        //var request = gapi.client.plus.people.get({
        //  'userId': 'me'
        //});
        //request.then(function(resp) {
        //  console.log(resp);
        //}, function(reason) {
        //  console.log('Error: ' + reason.result.error.message);
        //});

        var request = gapi.client.plus.people.list({
          'userId': 'me',
          'collection': 'visible'
        });

        request.execute(function (resp) {
          console.log(resp);
        });
      });
    }


    function moveMarkerToLocation(id, latlng) {
      for (var k in markers) {
        if (markers[k].id == id) {
          markers[k].marker.setLatLng(latlng);
        }
      }
    }
  }
})();
