(function ($) {
    $(document).ready(function () {
        $('.mkephem-meta-keywords-input').each(function () {
            var $textarea = $(this);
            var previewId = $textarea.data('preview-id');
            var $preview = $('#' + previewId);

            function updatePreview() {
                var content = $.trim($textarea.val());
                if (content === '') {
                    $preview.text('<meta name="keywords" content="...">');
                } else {
                    var encoded = $('<div>').text(content).html();
                    $preview.text('<meta name="keywords" content="' + encoded + '">');
                }
            }

            $textarea.on('input', updatePreview);
            updatePreview();
        });
    });
})(jQuery);
