(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('SpotController', SpotController)
        .directive('spot', SpotDirective)
        .filter('seatgeek', function() {
            return function(inp) {
                if (inp) {
                    var arr = [];
                    for (var i = 0; i < inp.length; i++) {
                        if (inp[i].indexOf('https://seatgeek.com') !== 0) {
                            arr.push(inp[i]);
                        }
                    }
                    return arr;
                }
                return null;
            }
        });

    angular
        .module('zoomtivity')


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
    }

    /** @ngInject */
    function SpotController($location, $modal, $stateParams, Spot, SpotService, ScrollService, SpotReview, SpotComment, $state, MapService, $rootScope, $http, dialogs, API_URL, InviteFriends, Share, AsyncLoaderService, toastr, S3_URL) {
        var vm = this;
        var spot = null;
        if ($rootScope.openedSpot) {
            spot = $rootScope.openedSpot;
        } else {
            spot = vm.spot;
        }
        vm.API_URL = API_URL;
        vm.spot = SpotService.formatSpot(spot);
        vm.saveToCalendar = SpotService.saveToCalendar;
        vm.removeFromCalendar = SpotService.removeFromCalendar;
        vm.addToFavorite = SpotService.addToFavorite;
        vm.removeFromFavorite = SpotService.removeFromFavorite;
        vm.removeSpot = removeSpot;
        vm.setImage = setImage;
        vm.invite = openInviteModal;
        vm.share = openShareModal;
        vm.photoIndex = 0;
        vm.getPrice = getPrice;
        vm.amenitiesCount = Object.keys(vm.spot.amenities).length;
        vm.inputDate = {
            start_date: null,
            end_date: null
        }
        vm.priceDate = {
            start_date: null,
            end_date: null
        };
        vm.prices = null;

        if (!$rootScope.isMapState()) {
            $rootScope.isDrawArea = false;
        }

        if (vm.spot.web_sites && vm.spot.web_sites[0].length) {
            for (var i = 0; i < vm.spot.web_sites.length; i++) {
                if (vm.spot.web_sites[i].indexOf('https://seatgeek.com/venues') === 0) {
                    spot.venues = vm.spot.web_sites[i];
                } else if (vm.spot.web_sites[i].indexOf('https://seatgeek.com') === 0) {
                    spot.tickets = vm.spot.web_sites[i];
                }
            }
        }

        if($rootScope.$state.params.spot_slug && $rootScope.$state.params.spot_slug !== vm.spot.slug) {
            var user_id = $rootScope.$state.params.user_id;
            var spot_id = $rootScope.$state.params.spot_id;
            $location.path(user_id + '/spot/' + spot_id + '/' + vm.spot.slug);
        }

        if ($stateParams.spot_id) {
            ShowMarkers([vm.spot]);
        }

        if (vm.spot.hotel) {
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/info').then(function(data) {
                if (vm.amenitiesCount == 0) {
                    vm.spot.amenities = data.amenities;
                    vm.amenitiesCount = Object.keys(vm.spot.amenities).length;
                }
                vm.mergeByProperty(vm.spot.photos, data.photos, 'id');
                vm.spot.photos = _.union(vm.spot.photos, vm.spot.comments_photos);
            });
        }
        
        vm.reviews_total = vm.spot.total_reviews;
        vm.spot.rating = vm.spot.avg_rating;
        
        AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/ratings').then(function(data) {
            vm.reviews_total = data;
            vm.spot.rating = data.total.rating;
        });

        vm.initDates = function() {
            var now = new Date(Date.now());
            vm.inputDate.start_date = (now.getMonth() + 1) + "." + now.getDate() + "." + now.getFullYear();
            now.setDate(now.getDate() + 1);
            if (!vm.inputDate.end_date) {
                vm.inputDate.end_date = (now.getMonth() + 1) + "." + now.getDate() + "." + now.getFullYear();
            }
        };

        vm.startDateChanged = function() {
            var newDate = new Date(vm.priceDate.start_date);
            newDate.setDate(newDate.getDate() + 1);
            vm.priceDate.end_date = (newDate.getMonth() + 1) + "." + newDate.getDate() + "." + newDate.getFullYear();
            vm.inputDate.start_date = new Date(Date.now());
            vm.inputDate.end_date = vm.priceDate.end_date;
        }

        vm.mergeByProperty = function(arr1, arr2, prop) {
            _.each(arr2, function(arr2obj) {
                var arr1obj = _.find(arr1, function(arr1obj) {
                    return arr1obj[prop] === arr2obj[prop];
                });

                arr1obj ? _.extend(arr1obj, arr2obj) : arr1.push(arr2obj);
            });
        }

        vm.attachments = {
            photos: [],
            spots: [],
            areas: [],
            links: []
        };
        vm.openPhotosModal = function() {
            vm.photoModal = $modal.open({
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
            vm.photoModal.result.then(function() {
                // $rootScope.setOpenedSpot(null);
                $http.get(API_URL + '/spots/' + vm.spot.id)
                    .success(function success(data) {
                        vm.spot = data;
                    });
            });
        };

        function getPrice() {
            if (!vm.priceDate.start_date || !vm.priceDate.end_date) {
                toastr.error('Please select your dates.')
            } else {
                $http.get(API_URL + '/spots/' + spot.id + '/prices?' + $.param(vm.priceDate))
                    .then(function(response){
                        
                        if ( (response.data.data.booking == false) && (response.data.data.hotels == false))
                        {
                            toastr.info('No prices detected. Please try another dates.');
                        }
                        else
                        {
                            vm.prices = response.data.data;
                            vm.prices.diff = response.data.diff;
                        }
                    },function(response){
                        toastr.error('No response. Please try again later.');
                    });
            }
        }

        //MapService.GetMap().panTo(new L.LatLng(spot.points[0].location.lat, spot.points[0].location.lng));
        MapService.GetMap().setView(new L.LatLng(spot.points[0].location.lat, spot.points[0].location.lng));

        vm.close = function() {
            // vm.spot = null;
            var container = document.querySelector('.search-filters');
            $rootScope.setOpenedSpot(null);
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
        // ShowMarkers([vm.spot]);
        
        function getRandomInt(min, max)
        {
            return Math.floor(Math.random() * (max - min + 1)) + min;
        }

        function setImage() {
            var category = vm.category;
            var type = (category)?vm.spot.category.type.name:null;
            if ( category &&
                (type === 'food' || 
                 type === 'shelter')) 
            {
                if (false) 
                {
                    return vm.spot.cover_url.original;
                }
                else 
                {   var max = (type === 'food')?32:84;
                    var imgnum = getRandomInt(0, max);
                    return S3_URL + '/assets/img/placeholders/' + type + '/' + imgnum + '.jpg';
                }
            }
            else
            {
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
                if (message.attachments.album_photos) {
                    vm.spot.photos = _.union(vm.spot.photos, message.attachments.album_photos);
                }
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
})();
