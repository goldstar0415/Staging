(function () {
  'use strict';

  angular
    .module('zoomtivity')
    .factory('Friends', Friends);

  /** @ngInject */
  function Friends($resource, API_URL) {
    return $resource(API_URL + '/friends', {id: '@id'}, {
      update: {
        url: API_URL + '/friends/:id',
        transformRequest: function(data, headers) {
          var params = {
            first_name: data.first_name,
            last_name: data.last_name,
            birth_date: data.birth_date,
            phone: data.phone,
            email: data.email,
            location: data.location,
            address: data.address,
            note: data.note
          };
          return JSON.stringify(params);
        },
        method: 'PUT'
      },
      getFriend: {
        url: API_URL + '/friends/:id',
        method: "GET"
      },
      deleteFriend: {
        url: API_URL + '/friends/:id',
        method: "DELETE"
      },
      setAvatar: {
        url: API_URL + '/friends/:id',
        method: "POST"
      }
    });
  }

})();
