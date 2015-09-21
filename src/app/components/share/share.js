(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .directive('share', share);

  /** @ngInject */
  function share() {
    return {
      restrict: 'E',
      templateUrl: '/app/components/share/share.html',
      scope: {
        item: '=',
        type: '@'
      },
      controller: ShareController,
      controllerAs: 'share',
      bindToController: true
    };

    function ShareController($state, $location, $window) {
      var vm = this;
      var logo = $location.origin + '/assets/img/missing_thumb.png';

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
          vm.picture = logo;
          break;
        case 'post':
          vm.text = vm.item.title;
          vm.url = $state.href('blog.article', {slug: vm.item.slug}, {absolute: true});
          vm.picture = vm.item.cover_url.medium;
          break;
      }

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

      vm.google = function () {
        $window.open(vm.item.share_links.google, 'sharer', 'toolbar=0,status=0,width=650,height=650');
      };

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

  }
})();
