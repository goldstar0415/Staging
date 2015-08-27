(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('ScrollService', ScrollService);

  /** @ngInject */
  function ScrollService($http) {
    var Reddit = function(url, limit) {
      this.page = 1;
      this.limit = limit;
      this.items = [];
      this.busy = false;
      this.after = '';
    };

    Reddit.prototype.nextPage = function() {
      console.log(111);
      if (this.busy) return;
      this.busy = true;

      var url = "http://api.reddit.com/hot?after=" + this.after + "&jsonp=JSON_CALLBACK";
      $http.jsonp(url).success(function(data) {
        var items = data.data.children;
        for (var i = 0; i < items.length; i++) {
          this.items.push(items[i].data);
        }
        this.after = "t3_" + this.items[this.items.length - 1].id;
        this.busy = false;
      }.bind(this));
    };

    return Reddit;
  }

})();
