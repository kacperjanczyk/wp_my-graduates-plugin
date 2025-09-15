jQuery(document).ready(function($) {
    if (typeof wp.media === 'undefined') {
        console.error('wp.media is not available!');
        return;
    }

    $(document).on('click', '.js-select-media', function(e) {
        e.preventDefault();

        const l10n = window.myGraduatesL10n || {
            selectPhoto: 'Select Photo',
            useThisPhoto: 'Use this photo',
            removePhoto: 'Remove photo'
        };

        const frame = wp.media({
            title: l10n.selectPhoto,
            button: {
                text: l10n.useThisPhoto
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();

            if (attachment && attachment.url) {
                $('#graduate_photo').val(attachment.url);
                updatePhotoPreview(attachment.url, l10n.removePhoto);
            } else {
                console.error('No valid attachment selected');
            }
        });

        frame.open();
    });

    $(document).on('click', '.js-remove-media', function(e) {
        e.preventDefault();

        $('#graduate_photo').val('');
        $('#photo-preview').empty();
    });

    function updatePhotoPreview(url, removeText) {
        const preview = $('#photo-preview');
        const html = `
            <img src="${url}" 
                 alt="Graduate photo" 
                 style="max-width: 200px; height: auto; border: 1px solid #ddd; padding: 5px; margin-top: 10px;" />
            <br>
            <button type="button" class="button-link js-remove-media" style="margin-top: 5px;">
                ${removeText || 'Remove photo'}
            </button>
        `;

        preview.html(html);
    }
});
