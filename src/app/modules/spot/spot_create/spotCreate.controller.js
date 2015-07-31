(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .controller('SpotCreateController', SpotCreateController);

  /** @ngInject */
  function SpotCreateController(toastr) {
    var vm = this;
    vm.restrictions = {
      links: 5,
      tags: 7,
      locations: 20,
      images: 10,
      video: 5
    };
    vm.type = 'Event';

    //vars
    vm.tags = [];
    vm.links = [];
    vm.youtube_links = [];
    vm.locations = [];

    vm.create = function(form) {
      console.log(form.$valid);
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
      if(item && item.address && item.location) {
        vm.locations.unshift(item);
        vm.newLocation = {};
      } else {
        toastr.error('You can\'t add empty location');
        vm.newLocation = {};
      }
    };
    vm.removeLocation = function(index) {
      vm.locations.splice(index, 1);
    };
    vm.onChange = function() {
      alert('test');
    };
    //photos

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
