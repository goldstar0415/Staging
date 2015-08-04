(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotCreateController', SpotCreateController);

  /** @ngInject */
  function SpotCreateController(toastr, $scope, MapService, UploaderService, CropService, moment) {
    var vm = this;
    vm.restrictions = {
      links: 5,
      tags: 7,
      locations: 20,
      images: 10,
      video: 5
    };
    vm.type = 'Event';
    vm.selectCover = false;
    vm.startDate = moment().toDate();

    //vars
    vm.tags = [];
    vm.links = [];
    vm.youtube_links = [];
    vm.locations = [];
    vm.images = UploaderService.images;

    $scope.$watch('SpotCreate.images.files', function() {
      vm.checkFilesRestrictions();
    });

    function filterLocations() {
      var array = _.reject(vm.locations, function(item) {
          return item.isDelete || false;
        });

      array = _.map(array, function(item) {
        return {location: item.location, address: item.address};
      });

      return array;
    }
    function filterTags() {
      var array = _.map(vm.tags, function(item) {
        return item.text;
      });

      return array;
    }

    vm.create = function(form) {
      if(form.$valid) {
        var request = {};
        request.title = vm.title;
        request.description = vm.description;
        request.web_site = vm.links;
        request.tags = filterTags();
        request.videos = vm.youtube_links;
        request.location = filterLocations();
        request.files = vm.images.files;
        request.cover = vm.cover;


        if(vm.type === 'Event') {
          request.start_date = vm.start_date + ' ' + vm.start_time + ':00';
          request.end_date = vm.end_date + ' ' + vm.end_time + ':00';
        }

        if(request.location.length > 0) {
          console.log(request);
        } else {
          toastr.error('Please add at least one location');
        }
      } else {
        toastr.error('Invalid input');
      }

    };
    //links
    vm.addLink = function(validLink) {
      if(validLink && vm.newLink) {
        vm.links.unshift(vm.newLink);
        vm.newLink = '';
      } else {
        toastr.error('Link is not valid');
        vm.newLink = '';
      }
    };
    vm.removeLink = function(index) {
      vm.links.splice(index, 1);
    };
    //tags
    vm.onTagsAdd = function() {
      if(vm.tags.length < vm.restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + vm.restrictions.tags + ' tags');
        return false;
      }
    };
    //location
    vm.addLocation = function(item) {
      var item = angular.copy(item);
      if(item && item.address && item.location) {
        vm.locations.unshift(item);
        vm.newLocation = {};
      } else {
        toastr.error('You can\'t add empty location');
        vm.newLocation = {};
      }
    };
    vm.removeLocation = function(index) {
      var marker = vm.locations[index].marker;
      MapService.RemoveMarker(marker);
      vm.locations[index].isDelete = true;
    };
    //photos
    vm.checkFilesRestrictions = function() {
      if(vm.images.files.length > vm.restrictions.images) {
        toastr.error('You can\'t add more than '+ vm.restrictions.images + ' photos');
        var l = vm.images.files.length - vm.restrictions.images;
        vm.images.files.splice(vm.restrictions.images, l);
      }
    };
    vm.deleteImage = function (idx) {
      vm.images.files.splice(idx, 1);
    };
    vm.cropImage = function(image){
      if(vm.selectCover) {
        CropService.crop(image, function(result) {
          if(result) {
            vm.cover = result;
            vm.selectCover = false;
          }
        });
      }
    };

    //videos
    vm.addYoutubeLink = function(validLink) {
      if(validLink && vm.newYoutubeLink) {
        vm.youtube_links.unshift(vm.newYoutubeLink);
        vm.newYoutubeLink = '';
      } else {
        toastr.error('Link is not valid');
        vm.newYoutubeLink = '';
      }
    };
    vm.removeYoutubeLink = function(index) {
      vm.youtube_links.splice(index, 1);
    };

  }
})();
