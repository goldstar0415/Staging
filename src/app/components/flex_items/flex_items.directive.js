(function() {
    'use strict';

    angular
        .module('zoomtivity')
        .directive('flexitems', FlexItems);

    /** @ngInject */
    function FlexItems() {
        return {
            restrict: 'A',
            controller: FlexItemsController,
            controllerAs: 'FlexItems',
            bindToController: true,
            link: function(scope, element, attrs) {
                var observer = new MutationObserver(function(mutations) {
                    function checkCount(current_total, next) {
                        if (current_total % 12) {
                            current_total += 1;
                            checkCount(current_total, next);
                        } else {
                            next(current_total);
                        }
                    }

                    function addDummyElementsToItems(container) {
                        var card_count = container.children.length;
                        checkCount(card_count, function(final_count) {
                            var dummy_element;
                            for (var i = 0; i < (final_count - card_count); i++) {
                                dummy_element = document.createElement('div');
                                dummy_element.className = 'item item-fake';
                                container.appendChild(dummy_element);
                            }
                        });
                    }

                    addDummyElementsToItems(element[0]);
                });
                observer.observe(element[0], {
                    childList: true,
                    subtree: true
                });
            }
        };

        /** @ngInject */
        function FlexItemsController($scope, Spot, $rootScope) {

        }
    }
})();
