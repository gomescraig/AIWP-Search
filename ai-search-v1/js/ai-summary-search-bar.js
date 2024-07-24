jQuery(document).ready(function($) {
    $('#ai-summary-search-form').on('submit', function(e) {
        e.preventDefault();

        var query = $('#ai-summary-search-input').val();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ai_summary_search_bar',
                query: query
            },
            success: function(response) {
                if (response.success) {
                    $('#ai-summary-search-results').html('<p>' + response.data.summary + '</p>');
                } else {
                    $('#ai-summary-search-results').html('<p>Error: ' + response.data + '</p>');
                }
            },
            error: function() {
                $('#ai-summary-search-results').html('<p>Error during request</p>');
            }
        });
    });
});