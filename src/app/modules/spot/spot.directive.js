(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .directive('spot', SpotDirective);


    function SpotDirective() {
        return {
            restrict: 'E',
            templateUrl: '/app/modules/spot/spot.html',
            scope: {
                spot: '=',
                modal: '='
            },
            controller: SpotController,
            controllerAs: 'Spot',
            bindToController: true
        };

        /** @ngInject */
        function SpotController($modal, $scope, SpotService, ScrollService, SpotReview, SpotComment, $state, $http, MapService, $rootScope, dialogs, API_URL, InviteFriends, Share, AsyncLoaderService) {
            var vm = this;
            var spot = vm.spot;
            vm.API_URL = API_URL;
            vm.saveToCalendar = SpotService.saveToCalendar;
            vm.removeFromCalendar = SpotService.removeFromCalendar;
            vm.addToFavorite = SpotService.addToFavorite;
            vm.removeFromFavorite = SpotService.removeFromFavorite;
            vm.removeSpot = removeSpot;
            vm.close = close;
            vm.tab = 'overview';
            vm.chatterTab = 'zoomtivity';
            vm.scroll = 0;
            vm.setImage = setImage;
            vm.invite = openInviteModal;
            vm.share = openShareModal;
            vm.photoIndex = 0;
            vm.getPrice = getPrice;
            vm.amenitiesCount = Object.keys(vm.spot.amenities).length;
            vm.priceDate = {
                start_date: null,
                end_date: null
            };
            vm.prices = null;
            vm.attachments = {
                photos: [],
                spots: [],
                areas: [],
                links: []
            };
            
            if( vm.spot.hotel )
            {
                AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/info').then(function(data) {
                    if(vm.amenitiesCount == 0)
                    {
                        vm.spot.amenities = data.amenities;
                        vm.amenitiesCount = Object.keys(vm.spot.amenities).length;
                    }
                    vm.mergeByProperty(vm.spot.photos, data.photos, 'id');
                    vm.spot.photos = _.union(vm.spot.photos, vm.spot.comments_photos);
                });
            }
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/ratings').then(function(data) {
                vm.reviews_total = data;
                vm.spot.rating = data.total.rating;
                syncSpots(spot.id, {is_saved: true});
            });
            
            vm.mergeByProperty = function(arr1, arr2, prop) {
                _.each(arr2, function(arr2obj) {
                    var arr1obj = _.find(arr1, function(arr1obj) {
                        return arr1obj[prop] === arr2obj[prop];
                    });

                    arr1obj ? _.extend(arr1obj, arr2obj) : arr1.push(arr2obj);
                });
            }

            vm.openPhotosModal = function() {
                $modal.open({
                    templateUrl: '/app/components/ng_input/photos_modal.html',
                    controller: 'PhotosModalController',
                    controllerAs: 'modal',
                    modalContentClass: 'clearfix',
                    resolve: {
                        url: function() {
                            return API_URL + '/spots/' + spot.id + '/photos';
                        },
                        albums: function(Album) {
                            return Album.query({
                                user_id: $rootScope.currentUser.id
                            }).$promise;
                        },
                        attachments: function() {
                            return vm.attachments;
                        }
                    }
                });
            };

            function getPrice() {
                $http.get(API_URL + '/spots/' + spot.id + '/prices?' + $.param(vm.priceDate))
                    .success(function success(data) {
                        vm.prices = data.data;
                    });
            }

            //MapService.GetMap().panTo(new L.LatLng(spot.points[0].location.lat, spot.points[0].location.lng));
            MapService.GetMap().setView(new L.LatLng(spot.points[0].location.lat, spot.points[0].location.lng));

            $scope.$watch(function() {
                return angular.element('.opened-item').is(':visible')
            }, function() {
                var container = document.querySelector('.search-filters');
                vm.scroll = container.scrollTop;
                container.scrollTop = 0;
            });

            function close() {
                vm.spot = null;
                var container = document.querySelector('.search-filters');
                // container.scrollTop = vm.scroll;
            }

            function openInviteModal(item) {
                InviteFriends.openModal(item);
            }

            function openShareModal(item, type) {
                Share.openModal(item, type);
            }

            vm.postComment = postComment;
            vm.deleteComment = deleteComment;

            $rootScope.syncSpots = {
                data: [vm.spot]
            };
            $rootScope.currentSpot = vm.spot;

            vm.votes = {};

            vm.comments = {};
            var params = {
                page: 0,
                limit: 10,
                spot_id: spot.id
            };
            vm.pagination = new ScrollService(SpotComment.query, vm.comments, params);
            vm.reviewsPagination = new ScrollService(SpotReview.query, vm.votes, params);

            function setImage() {
                if (vm.spot.category.type.name === 'food') {
                    if (false) {
                        return vm.spot.cover_url.original;
                    } else {
                        var imgnum = Math.floor(vm.spot.id % 33);
                        return '../../../assets/img/placeholders/food/' + imgnum + '.jpg';
                    }
                } else {
                    return vm.spot.cover_url.original;
                }
            }

            /*
             * Delete spot
             * @param spot {Spot}
             * @param idx {number} spot index
             */
            function removeSpot(spot, idx) {
                SpotService.removeSpot(spot, idx, function() {
                    $state.go('spots', {
                        user_id: $rootScope.currentUser.id
                    });
                });
            }

            //send new comment for spot
            function postComment() {
                SpotComment.save({
                    spot_id: spot.id
                }, {
                    body: vm.message || '',
                    attachments: {
                        album_photos: _.pluck(vm.attachments.photos, 'id'),
                        spots: _.pluck(vm.attachments.spots, 'id'),
                        areas: _.pluck(vm.attachments.areas, 'id'),
                        links: vm.attachments.links
                    }
                }, function success(message) {
                    vm.comments.data.unshift(message);
                    vm.message = '';
                    vm.attachments.photos = [];
                    vm.attachments.spots = [];
                    vm.attachments.areas = [];
                    vm.attachments.links = [];
                }, function error(resp) {
                    console.warn(resp);
                    toastr.error('Send message failed');
                })
            }

            //show markers on map
            function ShowMarkers(spots) {
                var spotsArray = _.map(spots, function(item) {
                    return {
                        id: item.id,
                        spot_id: item.spot_id,
                        locations: item.points,
                        address: '',
                        spot: item
                    };
                });
                MapService.drawSpotMarkers(spotsArray, 'other', true);
                MapService.FitBoundsOfCurrentLayer();
            }

            /*
             * Delete comment
             * @param comment {SpotComment}
             * @param idx {number} comment index
             */
            function deleteComment(comment, idx) {
                dialogs.confirm('Confirmation', 'Are you sure you want to delete comment?').result.then(function() {
                    SpotComment.delete({
                        spot_id: spot.id,
                        id: comment.id
                    }, function() {
                        toastr.info('Comment successfully deleted');
                        vm.comments.data.splice(idx, 1);
                    });
                });
            }
        }
    }
})();
