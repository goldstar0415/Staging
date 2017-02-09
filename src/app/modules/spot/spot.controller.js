(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('SpotController', SpotController)
        .directive('spot', SpotDirective)
        .directive('spotJsonld', SpotJsonld)
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
        })
        .filter('ticketmaster', function() {
            return function(inp) {
                if (inp) {
                    var arr = [];
                    for (var i = 0; i < inp.length; i++) {
                        if (inp[i].indexOf('ticketmaster.com/') === -1) {
                            arr.push(inp[i]);
                        }
                    }
                    return arr;
                }
                return null;
            }
        });

    function SpotJsonld($filter, $sce, $location) {
        return {
            restrict: 'E',
            scope: {
                spot: '='
            },
            replace: true,
            template: '<script type="application/ld+json" ng-bind-html="onGetJson()"></script>',
            link: function(scope, element, attrs) {
                scope.onGetJson = function() {
                    var event = {
                        "@context": "http://schema.org/",
                        "@type": "Event",
                        name: scope.spot.title,
                        description: scope.spot.description,
                        startDate: scope.spot.$start_date,
                        endDate: scope.spot.$end_date,
                        url: $location.absUrl(),
                    };
                    var geoPoint = scope.spot.points && scope.spot.points[0] ? scope.spot.points[0] : null;
                    if (geoPoint) {
                        event.location = {
                            "@type": "Place",
                            address: geoPoint.address,
                            name: geoPoint.address
                        };
                        if (geoPoint.location) {
                            event.location.geo = {
                                '@type': 'GeoCoordinates',
                                latitude: geoPoint.location.lat,
                                longitude: geoPoint.location.lng
                            }
                        }
                    }
                    if (scope.spot.cover_url) {
                        event.image = scope.spot.cover_url.thumb || scope.spot.cover_url.medium || scope.spot.cover_url.original;
                    }
                    if (scope.spot.rating) {
                        event.aggregateRating = {
                            ratingCount: scope.spot.rating
                        };
                    }
                    return $sce.trustAsHtml($filter('json')(event));
                }
            }
        };
    }

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
        vm.reviewsEnabled = true;
        vm.ratingsRefreshing = [];
        vm.rating = vm.spot.avg_rating;
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
                if ( vm.spot.web_sites[i].indexOf('https://seatgeek.com/venues') === 0 || /ticketmaster\.com\/venue/i.test(vm.spot.web_sites[i]) ) {
                    spot.venues = vm.spot.web_sites[i];
                } else if ( vm.spot.web_sites[i].indexOf('https://seatgeek.com') === 0 || /ticketmaster\.com\/event/i.test(vm.spot.web_sites[i]) ) {
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

        if (vm.spot.booking_url) {
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/info').then(function(data) {
                if (vm.amenitiesCount == 0) {
                    vm.spot.amenities = data.amenities;
                    vm.amenitiesCount = Object.keys(vm.spot.amenities).length;
                }
                vm.mergeByProperty(vm.spot.photos, data.photos, 'id');
                vm.spot.photos = _.union(vm.spot.photos, vm.spot.comments_photos);
            });
        }
        
        vm.reviews_total = {
            zoomtivity: {
                rating: parseFloat(vm.spot.rating),
                reviews_count: parseInt(vm.spot.reviews_count)
            },
            booking: {
                rating: parseFloat(vm.spot.booking_rating),
                reviews_count: parseInt(vm.spot.booking_reviews_count)
            },
            hotelscom: {
                rating: parseFloat(vm.spot.hotelscom_rating),
                reviews_count: parseInt(vm.spot.hotelscom_reviews_count)
            },
            yelp: {
                rating: parseFloat(vm.spot.yelp_rating),
                reviews_count: parseInt(vm.spot.yelp_reviews_count)
            },
            facebook: {
                rating: parseFloat(vm.spot.facebook_rating),
                reviews_count: parseInt(vm.spot.facebook_reviews_count)
            },
            tripadvisor: {
                rating: parseFloat(vm.spot.tripadvisor_rating),
                reviews_count: parseInt(vm.spot.tripadvisor_reviews_count)
            },
            google: {
                rating: parseFloat(vm.spot.google_rating),
                reviews_count: parseInt(vm.spot.google_reviews_count)
            },
            zomato: {
                rating: parseFloat(vm.spot.zomato_rating),
                reviews_count: parseInt(vm.spot.zomato_reviews_count)
            }
        };
        calcRatings();
        //vm.spot.rating = vm.spot.avg_rating;
        //vm.spot.rating = vm.spot.avg_rating;
        
        
        
        
        
        if (vm.spot.booking_url) {
            vm.reviewsEnabled = false;
            refreshRating('booking');
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/booking-rating').then(function(data) {
                if(data.booking)
                {
                    vm.reviews_total['booking'] = data.booking;
                }
                ratingRefreshed('booking');
                calcRatings();
                enableReviews();
            });
        }
        
        if (vm.spot.hotelscom_url) {
            vm.reviewsEnabled = false;
            refreshRating('hotelscom');
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/hotelscom-rating').then(function(data) {
                if(data.hotels)
                {
                    vm.reviews_total['hotelscom'] = data.hotelscom;
                }
                ratingRefreshed('hotelscom');
                calcRatings();
                enableReviews();
            });
        }
        
        if (vm.spot.yelp_url || vm.spot.yelp_id) {
            vm.reviewsEnabled = false;
            refreshRating('yelp');
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/yelp-rating').then(function(data) {
                if(data.yelp)
                {
                    vm.reviews_total['yelp'] = data.yelp;
                }
                ratingRefreshed('yelp');
                calcRatings();
                enableReviews();
            });
        }
        
        if (vm.spot.tripadvisor_url) {
            vm.reviewsEnabled = false;
            refreshRating('tripadvisor');
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/tripadvisor-rating').then(function(data) {
                if(data.tripadvisor)
                {
                    vm.reviews_total['tripadvisor'] = data.tripadvisor;
                }
                ratingRefreshed('tripadvisor');
                calcRatings();
                enableReviews();
            });
        }
        
        if (vm.spot.facebook_url) {
            vm.reviewsEnabled = false;
            refreshRating('facebook');
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/facebook-rating').then(function(data) {
                if(data.facebook)
                {
                    vm.reviews_total['facebook'] = data.facebook;
                }
                ratingRefreshed('facebook');
                calcRatings();
                enableReviews();
            });
        }
        
        if (vm.spot.google_id) {
            vm.reviewsEnabled = false;
            refreshRating('google');
            AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/google-rating').then(function(data) {
                if(data.google)
                {
                    vm.reviews_total['google'] = data.google;
                }
                ratingRefreshed('google');
                calcRatings();
                enableReviews();
            });
        }
        
        function calcRatings()
        {
            var $starsSumm = 0;
            var $reviewsCount = 0;
            var $weightedAvg = 0;
            _.each(vm.reviews_total, function(value, key) {
                if(value.rating && value.reviews_count && !isNaN(value.rating) && !isNaN(value.reviews_count))
                {
                    $weightedAvg += value.rating * value.reviews_count;
                    $starsSumm += value.rating;
                    $reviewsCount += value.reviews_count;
                }
                
            });
            
            var rating = 0;
            if($reviewsCount != 0)
            {
                rating = Math.round(parseFloat($weightedAvg/$reviewsCount)*10)/10;
            }
            vm.spot.avg_rating = rating;
            vm.spot.total_reviews = $reviewsCount;
            vm.rating = vm.spot.avg_rating;
        }
        
        /*AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/ratings').then(function(data) {
            vm.reviews_total = data;
            vm.spot.rating = data.total.rating;
        });*/
        
        AsyncLoaderService.load(API_URL + '/spots/' + spot.id + '/hours').then(function(data) {
            vm.spot.hours = data;
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
        
        function enableReviews() {
            if(vm.ratingsRefreshing.length === 0)
            {
                vm.reviewsEnabled = true;
                $http.post(API_URL + '/spots/' + spot.id + '/save-rating', {
                    avg_rating: vm.spot.avg_rating,
                    total_reviews: vm.spot.total_reviews
                }, {});
                /*vm.votes = {};
                vm.reviewsPagination = new ScrollService(SpotReview.query, vm.votes, {
                    page: 0,
                    limit: 10,
                    spot_id: spot.id
                });*/
            }
        }
        
        function refreshRating(serviceName) {
            vm.ratingsRefreshing.push(serviceName);
        }
        
        function ratingRefreshed(serviceName) {
            var index = vm.ratingsRefreshing.indexOf(serviceName);
            vm.ratingsRefreshing.splice(index, 1);
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
