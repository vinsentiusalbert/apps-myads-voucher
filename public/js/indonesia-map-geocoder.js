(function () {
    const normalizeIndonesiaAddress = (value) => String(value || '')
        .replace(/\bjl\.?\s+/gi, 'Jalan ')
        .replace(/\b(?:no|nomor)\.?\s*[\w/-]+\s*,?/gi, '')
        .replace(/\bblok\s+[\w/-]+\s*,?/gi, '')
        .replace(/\bsouth tangerang city\b/gi, 'Tangerang Selatan')
        .replace(/\bsouth tangerang\b/gi, 'Tangerang Selatan')
        .replace(/\s*,\s*,+/g, ', ')
        .replace(/\s{2,}/g, ' ')
        .replace(/^\s*,|,\s*$/g, '')
        .trim();

    window.createIndonesiaGeocoder = function (L) {
        const geocoder = L.Control.Geocoder.nominatim({
            geocodingQueryParams: {
                countrycodes: 'id',
                addressdetails: 1,
                limit: 10,
                'accept-language': 'id',
            },
        });
        const geocode = geocoder.geocode.bind(geocoder);

        geocoder.geocode = function (query, callback, context) {
            return geocode(query, (results) => {
                if (results.length > 0) {
                    callback.call(context, results);
                    return;
                }

                const fallbackQuery = normalizeIndonesiaAddress(query);
                if (!fallbackQuery || fallbackQuery === String(query).trim()) {
                    callback.call(context, []);
                    return;
                }

                geocode(fallbackQuery, callback, context);
            }, context);
        };

        return geocoder;
    };
})();
