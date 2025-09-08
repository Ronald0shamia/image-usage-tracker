// Skripte für die Medienbibliothek
jQuery(document).ready(function($) {
    function loadImages(paged = 1) {
        const search = $('#image-search').val();
        const filter = $('#image-filter').val();

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'image_usage_load_images',
                security: imageUsage.nonce,
                paged: paged,
                search: search,
                filter: filter
            },
            beforeSend: function() {
                $('#image-usage-table tbody').html('<tr><td colspan="4">Lade Bilder...</td></tr>');
            },
            success: function(response) {
                if (!response.success) return;

                const tbody = $('#image-usage-table tbody');
                tbody.empty();

                if (response.images.length === 0) {
                    tbody.append('<tr><td colspan="4">Keine Bilder gefunden.</td></tr>');
                } else {
                    response.images.forEach(function(image) {
                        tbody.append(`
                            <tr>
                                <td><img src="${image.url}" width="60" style="border-radius:6px"></td>
                                <td>${image.filename}</td>
                                <td>${image.count}</td>
                                <td><a href="${image.link}" class="button button-primary">Verwendung anzeigen</a></td>
                            </tr>
                        `);
                    });
                }

                // Pagination aktualisieren
                const pagination = $('#image-usage-pagination');
                pagination.empty();
                if (response.paged > 1) {
                    pagination.append(`<button class="button prev-page">« Zurück</button>`);
                }
                if (response.has_more) {
                    pagination.append(`<button class="button next-page">Weiter »</button>`);
                }

                // Events für Pagination-Buttons
                $('.prev-page').on('click', function() {
                    loadImages(response.paged - 1);
                });
                $('.next-page').on('click', function() {
                    loadImages(response.paged + 1);
                });
            }
        });
    }

    // Suche / Filter bei Änderungen neu laden
    $('#image-search, #image-filter').on('change keyup', function() {
        loadImages(1);
    });

    // Initial laden
    loadImages();
});
