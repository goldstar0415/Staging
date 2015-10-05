(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotCreateController', SpotCreateController);

  /** @ngInject */
  function SpotCreateController(spot, $stateParams, $state, toastr, $scope, MapService, UploaderService, CropService, $timeout, moment, API_URL, $http, DATE_FORMAT, categories) {
    var vm = this;
    var coverName = null;

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
    vm.crop = {
      width: 512,
      height: 256
    };
    vm.firstload = true;
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
    $scope.$watch('SpotCreate.start_date', function () {
      if (!vm.firstload) {
        if (vm.start_date) {
          var start = moment(vm.start_date, DATE_FORMAT.date);
          var end = moment(vm.end_date, DATE_FORMAT.date);

          if (start.isAfter(end, 'seconds')) {
            vm.end_date = '';
          }
        }
      } else {
        vm.firstload = false;
      }
    });
    $scope.$watch('SpotCreate.type', function () {
      if (!vm.edit) {
        vm.category_id = '';
        vm.start_date = null;
        vm.start_time = null;
        vm.end_date = null;
        vm.end_time = null;
      }
    });


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

      if (vm.newLocation && vm.newLocation.address) {
        vm.addLocation(vm.newLocation);
      }
      if (form.$valid && vm.category_id !== '') {
        var tags = filterTags();
        var locations = filterLocations();
        var request = {};
        request.title = vm.title;
        request.description = vm.description;
        request.spot_type_category_id = vm.category_id;


        if (vm.cover) {
          request.cover = vm.cover;

          if (!vm.edit) {
            request.cover = vm.cover;
          }
        }
        if (vm.links && vm.links.length > 0) {
          if (vm.newLink) {
            vm.links.push(vm.newLink);
          }
          request.web_sites = vm.links;
        }
        if (vm.youtube_links && vm.youtube_links.length > 0) {
          if (vm.newYoutubeLink) {
            vm.youtube_links.push(vm.newYoutubeLink);
          }
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
        if (vm.type === 'event') {
          request.start_date = moment(vm.start_date + ' ' + vm.start_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.datepicker.time).format(DATE_FORMAT.backend);
          request.end_date = moment(vm.end_date + ' ' + vm.end_time, DATE_FORMAT.date + ' ' + DATE_FORMAT.datepicker.time).format(DATE_FORMAT.backend);
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
          vm.loading = true;
          UploaderService
            .upload(url, req)
            .then(function (resp) {
              if (vm.type != 'event') {
                toastr.info('Your submittal is under review and will be posted shortly.');

                $timeout(function () {
                  $state.go('spots', {user_id: resp.data.user_id});
                }, 3000);
              } else {
                $state.go('spot', {spot_id: resp.data.id, user_id: resp.data.user_id});
              }
            })
            .catch(function (resp) {
              vm.loading = false;
              toastr.error('Upload failed');
            });
        }
      } else {
        if (!vm.title) {
          toastr.error('Title is required!');
        } else if (!vm.category_id) {
          toastr.error('Category is required!');
        }

        if (vm.type === 'Event') {
          if (!vm.start_date) {
            toastr.error('Start date is required!');
          } else if (!vm.end_date) {
            toastr.error('End date is required!');
          } else if (!vm.start_time) {
            toastr.error('Start time is required!');
          } else if (!vm.end_time) {
            toastr.error('End time is required!');
          }
        }
      }

    };

    //links
    vm.addLink = function (validLink) {
      if (validLink && vm.newLink) {
        vm.links.unshift(vm.newLink);
        vm.newLink = null;
      } else {
        toastr.error('Link is not valid');
        vm.newLink = null;
      }
    };
    vm.removeLink = function (index) {
      vm.links.splice(index, 1);
    };
    //tags
    vm.onTagsAdd = function (q, w, e) {
      if (vm.tags.length < vm.restrictions.tags) {
        return true;
      } else {
        toastr.error('You can\'t add more than ' + vm.restrictions.tags + ' tags');
        return false;
      }
    };
    //location
    vm.addLocation = function (item) {
      if (item && item.address && item.location) {
        var item = angular.copy(item);

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
      if (vm.images.files.length > 0 && !vm.cover) {
        _setCover(vm.images.files[0]);
      }
    };

    function _setCover(image, id) {
      if (typeof image === 'string') {
        vm.cropCover = image;
        coverName = id;
      } else if (image.photo_url) {
        vm.cropCover = image.photo_url.original;
        coverName = image.id;
      } else {
        var reader = new FileReader();
        reader.onloadend = function () {
          vm.cropCover = reader.result;
          $scope.$apply();
        };
        coverName = image.name;
        reader.readAsDataURL(image);
      }
    }

    vm.deleteImage = function (idx, id) {
      if (id) {
        if (vm.images.files[idx].id == coverName) {
          vm.cropCover = '';
          vm.cover = null;
        }

        vm.deletedImages.push(id);
        vm.images.files.splice(idx, 1);
      } else {
        if (vm.images.files[idx].name == coverName) {
          vm.cropCover = '';
          vm.cover = null;
        }
        vm.images.files.splice(idx, 1);
      }
    };

    vm.changeCover = function (image) {
      if (vm.selectCover) {
        if (image.photo_url) {
          _setCover(image.photo_url.original, image.id);
        } else {
          _setCover(image);
        }

        vm.selectCover = false;
      }
    };

    vm.InvalidTag = function (tag) {
      if (tag.name.length > 64) {
        toastr.error('Your tag is too long. Max 64 symbols.');
      } else {
        toastr.error('Invalid input.');
      }
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

      _setCover(data.cover_url.original, data.id);
    };

    if (vm.edit) {
      vm.convertSpot();
    }
  }
})
();
