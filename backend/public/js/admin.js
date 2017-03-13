(function ($) {
    $(function () {
        //select2 for autocomplete users
        $('#users').select2({
            ajax: {
                url: '/admin/email/users',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search_text: params.term
                    };
                },
                processResults: function (data, params) {
                    var items = [];

                    if (data && data.length > 0) {
                        data.forEach(function (item) {
                            items.push({
                                id: item.id,
                                text: item.first_name + ' ' + item.last_name
                            });
                        });
                    }
                    return {
                        results: items
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });

        //select2 for autocomplete location
        $('#location')
            .select2({
                ajax: {
                    url: '//maps.googleapis.com/maps/api/geocode/json',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            address: params.term,
                            sensor: false
                        };
                    },
                    processResults: function (data, params) {
                        var items = [];

                        if (data.results && data.results.length > 0) {
                            data.results.forEach(function (item) {
                                items.push({
                                    location: item.geometry.location,
                                    id: item.formatted_address,
                                    text: item.formatted_address
                                });
                            });
                        }

                        return {
                            results: items
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            })
            .on("select2:select", function (e) {
                var location = e.params.data.location;
                $('#location_lat').val(location.lat);
                $('#location_lng').val(location.lng);
            })
        ;

        $(".new_multiple").select2({
            tags: true,
            tokenSeparators: [',', ' ']
        });
    });

//--------------------------------------------------
    $(document).ready(function () {
        if ($('#spot_type option').length) {
            hideDates();

            $('#spot_type').change(function () {
                var $spotsType;

                spotType();
                newTypeShowLog();
                deleteLogValue();

                function deleteLogValue() {
                    var $deleteLog = $('#deleteLog input[name="type"]');
                    var per = $deleteLog.attr('value', $spotsType);
                }

                function newTypeShowLog() {
                    function getLocation(href) {
                        var l = document.createElement("a");
                        l.href = href;
                        return l;
                    }

                    var $logLink = $('#log_link'),
                    link = $logLink.attr('href');
                    var parsedLink = getLocation(link);
                    var nLink = parsedLink.origin + parsedLink.pathname + '?type=' + $spotsType;
                    $logLink.attr('href', nLink);
                }

                function addSpotsCategory(spType) {
                    $.getJSON("/spots/categories?type=" + spType, function (data) {
                        $('option', $("#spot_category")).remove();

                        for (var obj in data) {
                            var displayName = data[obj].display_name;
                            var value = data[obj].id;
                            var category = document.getElementById("spot_category");
                            var option = document.createElement("option");
                            option.text = displayName;
                            option.value = value;
                            category.add(option);
                        }
                    });
                }

                function spotType() {
                    $spotsType = $("#spot_type option:selected").val();
                    addSpotsCategory($spotsType);

                    hideDates();
                }
            });
        }

        $('#limit').change(function () {
            var search = '';
            var val = $(this).val();
            if (location.search == "") {
                search = '?limit=' + val;
            } else {
                search = location.search.replace(/(&|\?)?limit=\d+/, '').replace(/(&|\?)?page=\d+/);
                search = (search == "") ? '?limit=' + val : search + '&limit=' + val;
            }
            location.href = location.origin + location.pathname + search;
        });

        $('form').submit(function (e) {
            var emptyinputs = $(this).find('input').filter(function () {
                return !$.trim(this.value).length;  // get all empty fields
            }).prop('disabled', true);
        });

        $('#bulk input[type=checkbox]').change(function (e) {
            var $row = $('.row-select');
            if ($(this).prop('checked')) {
                $row.prop('checked', true);
            } else {
                $row.prop('checked', false);
            }
        });

        $('#bulk-edit').submit(function (e) {
            var $form = $(this);
            $('.row-select:checked').each(function () {
                $("<input>").attr({
                    'type': 'hidden',
                    'name': 'spots[]'
                }).val($(this).val()).appendTo($form);
            });
        });

        $('#bulk-delete, #email-users').click(function (e) {
            $(this).attr('href', $(this).attr('href') + '?' + getBulkRows(true));
        });

        function hideDates() {
            var spotsType = $("#spot_type option:selected").val();

            if (spotsType == 'event') {
                $('.event-only').show();
            } else {
                $('.event-only').hide();
            }
        }

        function getBulkRows(queryFormat) {
            queryFormat = typeof queryFormat === 'undefined' ? false : queryFormat;

            var result = {};
            $('.row-select:checked').each(function (e) {
                var key = $(this).attr('name').replace('[]', '');
                if (!result.hasOwnProperty(key)) {
                    result[key] = [];
                }
                result[key].push($(this).val());
            });

            if (queryFormat) {
                return $.param(result);
            }

            return result;
        }
    });
})(jQuery);