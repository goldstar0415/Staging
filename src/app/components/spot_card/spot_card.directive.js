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
            
            function getRandomInt(min, max)
            {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }

            function getImg() {
                $http.get(API_URL + '/spots/' + vm.item.spot_id + '/cover')
                    .success(function success(data) {
                        if (data.cover_url) {
                            vm.image = data.cover_url.url;
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
                var category = item.category;
                var type = (category)?vm.spot.category.type.name:null;
                if (item.category_name === 'Food' || (category && (type == 'food' || type== 'shelter'))) {
                    if (false) {
                        return item.cover_url.original;
                    } else {
                        var max = (type === 'food')?32:84;
                        var imgnum = getRandomInt(0, max);
                        return S3_URL + '/assets/img/placeholders/' + type + '/' + imgnum + '.jpg';
                    }
                } else {
                    if (item.cover_url && item.cover_url.original !== "https://testback.zoomtivity.com/uploads/missings/covers/original/missing.png") {
                        return item.cover_url.original;
                    } else {
                        vm.getImg();
                        return "https://testback.zoomtivity.com/uploads/missings/covers/original/missing.png";
                    }
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
