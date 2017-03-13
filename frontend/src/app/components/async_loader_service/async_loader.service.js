(function () {
  'use strict';

    angular
        .module('zoomtivity')
        .factory('AsyncLoaderService', AsyncLoaderService);

    function AsyncLoaderService($http, $q) {
        this._data = [];
        this.$http = $http;
        this.$q = $q;
        
        this.load = function(Url) {
            if ( !this._data || !this._data[Url]) {
                this._data[Url] = this.$http.get(Url)
                .then(function(response) {
                  return response.data;
                }.bind(this));
            }

            return this._data[Url];
        };
        
        return {
            _data: this._data,
            $http: this.$http,
            $q: this.$q,
            load: this.load
        };
    }

})();