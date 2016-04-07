(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('ScrollService', ScrollService);

  /** @ngInject */
  function ScrollService($http, API_URL) {
    var Scroll = function (action, items, params) {
      this.action = action;
      this.items = items;
      this.params = params;
      this.busy = false;
      this.disabled = false;
    };

    Scroll.prototype.nextPage = function () {
      if (this.busy || this.disabled) return;
      if (_.isUndefined(this.totalItems) || this.totalItems > 0 && (this.params.page * this.params.limit) < this.totalItems) {
        this.busy = true;
        this.params.page++;

        this.action.call(this, this.params).$promise.then(function (resp) {
          if (!this.totalItems) {
            this.totalItems = resp.total;
          }
          this.items.data = this.items.data || [];
          this.items.data = _.union(this.items.data, resp.data);
          this.busy = false;
        }.bind(this));
      }
    };

    return Scroll;
  }

})();
