jQuery(document).ready(function($) {
    $('#short-url-form').submit(function(e) {
        e.preventDefault();

        var form = $(this);
        var urlInput = form.find('input[name="url"]');
        var resultContainer = $('#short-url-result');

        resultContainer.empty();

        if (urlInput.val() !== '') {
            $.ajax({
                url: form.attr('action'),
                type: form.attr('method'),
                data: form.serialize(),
                success: function(response) {
                    resultContainer.html('<p>Short URL: ' +
                            '<a href="' + response + '" target="_blank">' + response + '</a> ' +
                            '<i class="copy bi bi-clipboard2-check" onclick="'+copyToClipboard(response)+'"></i>' +
                        '</p>'
                    );
                    form.trigger('reset');
                },
                error: function() {
                    resultContainer.html('<p>An error occurred.</p>');
                }
            });
        }
    });

    function copyToClipboard(text) {
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(text).select();
        document.execCommand('copy');
        tempInput.remove();
    }
});
