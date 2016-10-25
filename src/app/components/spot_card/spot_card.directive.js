(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .directive('spotcard', SpotCard);

    /** @ngInject */
    function SpotCard() {
        return {
            restrict: 'A',
            templateUrl: '/app/components/spot_card/spot_card.html',
            scope: {
                item: '='
            },
            controller: SpotCardController,
            controllerAs: 'SpotCard',
            bindToController: true
        };

        /** @ngInject */
        function SpotCardController($rootScope, SpotService) {
            var vm = this;
            vm.addToFavorite = SpotService.addToFavorite;
            vm.removeFromFavorite = SpotService.removeFromFavorite;
            vm.image = setImage(vm.item);

            function setImage(item) {
                if (item.category.type.name === 'food') {
                    if (false) {
                        return item.cover_url.original;
                    } else {
                        var imgnum = Math.floor(item.id % 33);
                        return '../../../assets/img/placeholders/food/' + imgnum + '.jpg';
                    }
                } else {
                    return item.cover_url.original;
                }
            }
        }
    }
})();
