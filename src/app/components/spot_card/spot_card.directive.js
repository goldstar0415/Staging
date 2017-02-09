(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .directive('spotcard', SpotCard);

    /** @ngInject */
    function SpotCard() {
        return {
            restrict: 'A',
            templateUrl: '/app/components/spot_card/spot_card.html',
            scope: {
                item: '=',
                index: '='
            },
            controller: SpotCardController,
            controllerAs: 'SpotCard',
            bindToController: true
        };

        /** @ngInject */
        function SpotCardController($rootScope, $http, $scope, SpotService, MapService, API_URL, InviteFriends, Share, S3_URL) {
            var vm = this;
            vm.saveToCalendar = SpotService.saveToCalendar;
            vm.removeFromCalendar = SpotService.removeFromCalendar;
            vm.addToFavorite = SpotService.addToFavorite;
            vm.removeFromFavorite = SpotService.removeFromFavorite;
            vm.removeSpot = SpotService.removeSpot;
            vm.unFavorite = unFavorite;
            vm.getImg = getImg;
            vm.image = setImage(vm.item);
            vm.isMenuOpened = false;
            vm.toggleMenu = toggleMenu;
            vm.closeMenu = closeMenu;
            vm.openInviteModal = openInviteModal;
            vm.openShareModal = openShareModal;
            vm.openSpot = openSpot;

            if (vm.item.minrate) {
                if (!_.isEmpty(fx.rates)) {
                    vm.item.price = '$' + Math.round(fx(vm.item.minrate).from(vm.item.currencycode).to("USD"));
                } else {
                    vm.item.price = Math.round(vm.item.minrate) + ' ' + vm.item.currencycode;
                }
            }

            function getImg() {
                $http.get(API_URL + '/spots/' + ((vm.item.spot_id)?vm.item.spot_id:vm.item.id) + '/cover')
                    .success(function success(data) {
                        if (data.cover_url) {
                            vm.image = data.cover_url.original;
                        }
                    });
            }

            function openSpot(spotId, event) {
                if (!$rootScope.openedSpot) {
                    event.stopPropagation();
                    $http.get(API_URL + '/spots/' + spotId)
                        .success(function success(data) {
                            $rootScope.setOpenedSpot(data);
                        });
                }
            }

            function openInviteModal(item) {
                InviteFriends.openModal(item);
            }

            function openShareModal(item, type) {
                Share.openModal(item, type);
            }

            function toggleMenu() {
                vm.isMenuOpened = !vm.isMenuOpened;
            }

            function closeMenu() {
                vm.isMenuOpened = false;
            }
            
            function setImage(item) {
                if(item.type)
                {
                    var type = item.type;
                    var url = item.cover_url;
                }
                if(item.category)
                {
                    var type = item.category.type.name;
                    var url = item.cover_url.original;
                }
                var id = (item.spot_id) ? item.spot_id : item.id;
                var hc = false;
                if(typeof url == 'string' && url.length)
                {
                    var urlAttr = url.split('/');
                    var lastAttr = urlAttr[urlAttr.length - 1];
                    if(lastAttr != 'missing.png')
                    {
                        hc = true;
                    }
                }
                if (hc)
                {
                        return url;
                }
                if (type == 'food' || type== 'shelter') {
                    var max = (type === 'food')?32:84;
                    return S3_URL + '/assets/img/placeholders/' + type + '/' + (id % max) + '.jpg';
                } else {
                    vm.getImg();
                    return API_URL + "/uploads/missings/covers/original/missing.png";
                }
            }

            function unFavorite(spot, idx) {
              SpotService.removeFromFavorite(spot, function () {
                if ($rootScope.currentUser.id === $rootScope.profileUser.id) {
                  $scope.$parent.$parent.Favorite.spots.data.splice(idx, 1);
                  if ($scope.$parent.$parent.Favorite.markersSpots[idx].marker) {
                    MapService.GetCurrentLayer().removeLayer($scope.$parent.$parent.Favorite.markersSpots[idx].marker);
                  } else {
                    MapService.GetCurrentLayer().removeLayers($scope.$parent.$parent.Favorite.markersSpots[idx].markers)
                  }
                }
              })
            }
        }
    }
})();
