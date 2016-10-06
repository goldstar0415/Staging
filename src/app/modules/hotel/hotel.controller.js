(function () {
    'use strict';

    angular
        .module('zoomtivity')
        .controller('HotelController', HotelController);

    /** @ngInject */
    function HotelController(hotel, $state, toastr, API_URL, UploaderService, $rootScope, $http, moment, $scope) {
        var vm         = this;
        vm             = _.extend(vm, hotel);
        vm.loading     = false;
        vm.tomorrow    = moment().add(1, 'day');
        vm.threeMonth  = moment().add(3, 'month');
        
        vm.minDateFrom = vm.tomorrow;
        vm.maxDateFrom = vm.tomorrow;
        vm.minDateTo   = moment(vm.tomorrow).add(1, 'day');
        vm.maxDateTo   = vm.threeMonth;
        
        vm.start_date  = vm.tomorrow;
        vm.end_date    = moment(vm.tomorrow).add(1, 'day');
        
        vm.bookingUrl   = false;
        vm.hotelsUrl    = false;
        vm.hotelsPrice  = false;
        vm.bookingPrice = false;
        
        vm.firstSearch  = true;
        
        $scope.$watch('Hotel.start_date', function(){
            vm.minToChange();
        });
        $scope.$watch('Hotel.end_date', function(){
            vm.maxFromChange();
        });
        
        vm.maxFromChange = function() {
            vm.maxDateFrom = moment(vm.end_date).add(-1, 'day');
        };
        
        vm.minToChange = function() {
            vm.minDateTo = moment(vm.start_date).add(1, 'day');
        };
        
        vm.save = function(form) {
            if(form.$valid)
            {
                vm.hotelsPrice  = false;
                vm.bookingPrice = false;
                vm.hotelsUrl    = false;
                vm.bookingUrl   = false;
                vm.loading = true;
                vm.firstSearch = false;
                
                $http.get(API_URL + '/hotels/' + vm.id + '/prices?' + jQuery.param({
                    start_date: vm.start_date,
                    end_date: vm.end_date
                })).success(function (response) {
                    if( !vm.remote_photos.length )
                    {
                        vm.remote_photos = response.data.remote_photos;
                    }
                    if( Object.keys(response.data.amenitiesArray).length )
                    {
                        vm.amenitiesArray = response.data.amenitiesArray;
                    }
                    vm.bookingUrl = response.data.bookingUrl;
                    vm.hotelsPrice = response.data.hotels;
                    vm.bookingPrice = response.data.booking;
                    vm.hotelsUrl = response.data.hotelsUrl;
                    vm.description = response.result.description;
                    vm.loading = false;
                });
            }
        };
    }
})();