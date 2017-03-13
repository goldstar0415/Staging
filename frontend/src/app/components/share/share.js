(function () {
  'use strict';

  /*
   * Directive for social sharing
   */
  angular
    .module('zoomtivity')
    .factory('Share', ShareController);

// /app/components/share/share.html

    /** @ngInject */
    function ShareController($modal, $rootScope, SignUpService, $state, $location, $window) {
        var vm = this;
        vm.item = null;
        vm.type = null;

        vm.openModal = openModal;

        function openModal(item, type) {
            vm.item = item;
            vm.type = type;
            if (!$rootScope.currentUser) {
                SignUpService.openModal('SignUpModal.html');
                return;
            }

            $modal.open({
                templateUrl: '/app/components/share/share.html',
                controller: ShareModalController,
                controllerAs: 'modal',
                modalClass: 'authentication',
                resolve: {
                    type: function() {
                        return vm.type;
                    },
                    item: function() {
                        return vm.item;
                    }
                }
            });
        };

        return {
            openModal: openModal
        }
    }

    function ShareModalController(item, type, $modalInstance, $state, $location, $window) {
        var vm = this;
        vm.item = item;
        vm.type = type;
        //   debugger;
      switch (vm.type) {
        case 'spot':
          vm.text = vm.item.title;
          vm.description = vm.item.description;
          vm.url = $state.href('spot', {spot_id: vm.item.id, user_id: vm.item.user_id}, {absolute: true});
          vm.picture = vm.item.cover_url.medium;
          break;
        case 'area':
          vm.text = vm.item.title;
          vm.url = $state.href('areas.preview', {area_id: vm.item.id}, {absolute: true});
          vm.picture = vm.item.cover_url.medium;
          break;
        case 'post':
          vm.text = vm.item.title;
          vm.url = $state.href('blog.article', {slug: vm.item.slug}, {absolute: true});
          vm.picture = vm.item.cover_url.medium;
          break;
      }

      vm.close = function () {
        $modalInstance.close();
      };

      //share facebook
      vm.facebook = function () {
        FB.ui({
          method: 'feed',
          link: vm.url,
          picture: vm.picture,
          //name: attr.name,
          caption: vm.text,
          description: vm.description
          //properties: attr.properties,
          //actions: attr.actions
        });
      };

      //share google
      vm.google = function () {
        $window.open(vm.item.share_links.google, 'sharer', 'toolbar=0,status=0,width=650,height=650');
      };

      //share twitter
      vm.twitter = function () {
        var urlString = 'https://www.twitter.com/intent/tweet?';

        if (vm.text) {
          urlString += 'text=' + encodeURIComponent(vm.text);
        }

        //if (data.via) {
        //  urlString += '&via=' + encodeURI(data.via);
        //}

        //if (data.hashtags) {
        //  urlString += '&hashtags=' + encodeURI(data.hashtags);
        //}

        //default to the current page if a URL isn't specified
        urlString += '&url=' + encodeURIComponent(vm.url || $location.absUrl());

        $window.open(urlString, 'sharer', 'toolbar=0,status=0,width=900,height=650');
      }
    }
})();
