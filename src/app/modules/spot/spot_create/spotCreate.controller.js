(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotCreateController', SpotCreateController);

  /** @ngInject */
  function SpotCreateController(spot, $stateParams, $state, toastr, $scope, MapService, UploaderService, CropService, moment, API_URL, $http, DATE_FORMAT, categories) {
    var vm = this;
    vm.deletedImages = [];
    vm.edit = $state.current.edit || false;
    vm.restrictions = {
      links: 5,
      tags: 7,
      locations: 20,
      images: 10,
      video: 5
    };
    vm.type = 'event';
    vm.selectCover = false;
    vm.category_id = '';
    vm.startDate = moment().toDate();
    vm.categories = {};

    _.each(categories.data, function (item) {
      vm.categories[item.name] = item.categories;
    });


    //vars
    vm.tags = [];
    vm.links = [];
    vm.youtube_links = [];
    vm.locations = [];
    vm.images = UploaderService.images;

    //load data for editing


    $scope.$watch('SpotCreate.images.files.length', function () {
      vm.checkFilesRestrictions();
    });
    $scope.$watch('SpotCreate.start_date', function() {
      vm.end_date = '';
    });
    if(!vm.edit) {
      $scope.$watch('SpotCreate.type', function () {
        vm.category_id = '';
      });
    }

    function filterLocations() {
      var array = _.reject(vm.locations, function (item) {
        return item.isDelete || false;
      });

      array = _.map(array, function (item) {
        return {location: item.location, address: item.address};
      });

      return array;
    }

    function filterTags() {
      var array = _.map(vm.tags, function (item) {
        return item.name;
      });

      return array;
    }

    function rejectOldFiles() {
      return _.reject(vm.images.files, function (item) {
        return item.id ? true : false;
      })
    }

    vm.create = function (form) {
      form.$submitted = true;
      if (form.$valid && vm.category_id !== '') {
        var tags = filterTags();
        var locations = filterLocations();
        var request = {};
        request.title = vm.title;
        request.description = vm.description;
        request.spot_type_category_id = vm.category_id;

        if (vm.cover) {
          request.cover = vm.cover;
        }
        if (vm.links && vm.links.length > 0) {
          request.web_sites = vm.links;
        }
        if (vm.youtube_links && vm.youtube_links.length > 0) {
          request.videos = vm.youtube_links;
        }
        if (locations.length > 0) {
          request.locations = locations;
        }
        if (tags.length > 0) {
          request.tags = tags;
        }
        if (vm.edit) {
          request.deleted_files = vm.deletedImages;
        }
        if (vm.type === 'Event') {
          request.start_date = moment(vm.start_date + ' ' + vm.start_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.time).format(DATE_FORMAT.backend);
          request.end_date = moment(vm.end_date + ' ' + vm.end_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.time).format(DATE_FORMAT.backend);
        }
        var url = API_URL + '/spots';
        var req = {};
        req.payload = JSON.stringify(request);
        if (vm.edit) {
          req._method = 'PUT';
          url = API_URL + '/spots/' + $stateParams.spot_id;
        }

        if (request.locations && request.locations.length > 0) {

          vm.images.files = rejectOldFiles();
          UploaderService
            .upload(url, req)
            .then(function (resp) {
              if(vm.type != 'event') {
                toastr.info('Your submittal is under review and will be posted shortly.');
              }
              $state.go('spot', {spot_id: resp.data.id, user_id: resp.data.user_id});
            })
            .catch(function (resp) {
              toastr.error('Upload failed');
            });
        } else {
          toastr.error('Please add at least one location');
        }
      } else {
        if(!vm.title) {
          return toastr.error('Title is required!');
        }

        if(!vm.category_id) {
          return toastr.error('Category is required!');
        }

        if (vm.type === 'Event') {
          if(!vm.start_date) {
            return toastr.error('Start date is required!');
          }
          if(!vm.end_date) {
            return toastr.error('End date is required!');
          }
          if(!vm.start_time) {
            return toastr.error('Start time is required!');
          }
          if(!vm.end_time) {
            return toastr.error('End time is required!');
          }
        }
      }

    };

    //links
    vm.addLink = function (validLink) {
      if (validLink && vm.newLink) {
        vm.links.unshift(vm.newLink);
        vm.newLink = '';
      } else {
        toastr.error('Link is not valid');
        vm.newLink = '';
      }
    };
    vm.removeLink = function (index) {
      vm.links.splice(index, 1);
    };
    //tags
    vm.onTagsAdd = function () {
      if (vm.tags.length < vm.restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + vm.restrictions.tags + ' tags');
        return false;
      }
    };
    //location
    vm.addLocation = function (item) {
      var item = angular.copy(item);
      if (item && item.address && item.location) {
        vm.locations.unshift(item);
        vm.newLocation = {};
      } else {
        toastr.error('You can\'t add empty location');
        vm.newLocation = {};
      }
    };
    vm.removeLocation = function (index) {
      var marker = vm.locations[index].marker;
      MapService.RemoveMarker(marker);
      vm.locations[index].isDelete = true;
    };

    //photos
    vm.checkFilesRestrictions = function () {
      if (vm.images.files.length > vm.restrictions.images) {
        toastr.error('You can\'t add more than ' + vm.restrictions.images + ' photos');
        var l = vm.images.files.length - vm.restrictions.images;
        vm.images.files.splice(vm.restrictions.images, l);
      }
    };

    vm.deleteImage = function (idx, id) {
      if (id) {
        vm.deletedImages.push(id);
        vm.images.files.splice(idx, 1);
      } else {
        vm.images.files.splice(idx, 1);
      }
    };

    vm.cropImage = function (image) {
      if (vm.selectCover) {
        CropService.crop(image, 512, 256, function (result) {
          if (result) {
            vm.cover = result;
            vm.selectCover = false;
          }
        });
      }
    };

    vm.editCover = function () {
      if (vm.cover) {
        CropService.crop(vm.cover, 512, 256, function (result) {
          if (result) {
            vm.cover = result;
            vm.selectCover = false;
          }
        });
      }
    };
    vm.InvalidTag = function () {
      toastr.error('Invalid input.');
    };
    //videos
    vm.addYoutubeLink = function (validLink) {
      if (validLink && vm.newYoutubeLink) {
        vm.youtube_links.unshift(vm.newYoutubeLink);
        vm.newYoutubeLink = '';
      } else {
        toastr.error('Link is not valid');
        vm.newYoutubeLink = '';
      }
    };
    vm.removeYoutubeLink = function (index) {
      vm.youtube_links.splice(index, 1);
    };
    //load data for spot editing
    vm.convertSpot = function () {
      //TODO: add array to display all kinds of images (attachments from albums, photos for upload, and old photos)
      var data = spot;
      vm.type = data.category.type.display_name.toLowerCase();
      vm.title = data.title;
      vm.description = data.description;
      vm.links = data.web_sites;
      vm.youtube_links = data.videos;
      vm.category_id = data.spot_type_category_id;
      vm.tags = data.tags || [];
      vm.cover = data.cover_url.original;
      vm.locations = data.points;
      if (data.photos) {
        for (var k in data.photos) {
          vm.images.files.push(data.photos[k]);
        }
      }
      if (data.start_date && data.end_date) {
        vm.start_date = data.start_date;
        vm.end_date = data.end_date;
        vm.start_time = data.start_date;
        vm.end_time = data.end_date;
      }

    };

    if (vm.edit) {
      vm.convertSpot();
    }
  }
})();
