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
    });
    $(".new_multiple").select2({
        tags: true,
        tokenSeparators: [',', ' ']
    });


//--------------------------------------------------
    $(document).ready(function () {
        $('#spot_type').change(function () {
            var $spotsType;

            spotType();

            newTypeShowLog();

            deleteLogValue();

            function deleteLogValue() {
                var $deleteLog = $('#deleteLog input[name="type"]');
                var per =  $deleteLog.attr('value', $spotsType);
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
                $spotsType = $("#spot_type option:selected").text().replace(/\s+/g, '').toLowerCase();
                addSpotsCategory($spotsType);
            }
        });
        $('#limit').change(function () {
            location.href = location.origin + location.pathname + '?limit=' + $(this).val();
        });
    });
})(jQuery);