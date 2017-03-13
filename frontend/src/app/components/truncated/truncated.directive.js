(function () {
    'use strict';

    angular.module('zoomtivity').directive('truncated', ['$timeout',
        function truncated($timeout) {
            return {
                restrict: 'A',
                scope: {
                    text: '='
                },
                link: function(scope, element, attributes) {
                    scope.$watch(attributes.dotdotdot, function() {
                        $timeout(function() {
                            element.dotdotdot({
                                callback	: function( isTruncated, orgContent ) {
                                    if(isTruncated)
                                    {
                                        element.addClass('review-more');
                                    }
                                }
                            });
                            $(window).resize(function() {
                                element.context.innerText = scope.text;
                                element.dotdotdot({
                                    callback	: function( isTruncated, orgContent ) {
                                        if(isTruncated)
                                        {
                                            element.addClass('review-more');
                                        }
                                        else
                                        {
                                            element.removeClass('review-more');
                                        }
                                    }
                                });
                            });
                        }, 400);
                    });
                }
            };
        }
    ]);
}());
