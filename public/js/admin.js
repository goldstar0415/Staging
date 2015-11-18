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
})(jQuery);