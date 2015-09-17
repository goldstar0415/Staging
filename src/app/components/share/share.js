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

      switch (vm.type) {
        case 'spot':
              vm.text = vm.item.title;
              vm.facebook_url = 'http://api.zoomtivity.com/spots/208/preview';
              vm.url = $state.href('spot', {spot_id: vm.item.id, user_id: vm.item.user_id}, {absolute: true});
              break;
      }

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

        $window.open(
          urlString,
          'sharer', 'toolbar=0,status=0,width=900,height=650'
        );
      }
    }

  }
})();
