<?php
/*
Plugin Name: AI Summary Search Bar
Description: A search bar that summarizes search results using AI from Hugging Face.
Version: 1.0
Author: Your Name
*/

// Register shortcode
function ai_summary_search_bar_shortcode() {
    return '<form id="ai-summary-search-form">
                <input type="text" id="ai-summary-search-input" placeholder="Ask a question...">
                <button type="submit">Search</button>
            </form>
            <div id="ai-summary-search-results"></div>';
}
add_shortcode('ai_summary_search_bar', 'ai_summary_search_bar_shortcode');

// Enqueue JavaScript
function ai_summary_search_bar_scripts() {
    wp_enqueue_script('ai-summary-search-bar-js', plugins_url('/js/ai-summary-search-bar.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('ai-summary-search-bar-js', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'ai_summary_search_bar_scripts');

// AJAX handler
function ai_summary_search_bar_ajax() {
    if (!isset($_POST['query'])) {
        wp_send_json_error('No query provided');
        return;
    }

    $query = sanitize_text_field($_POST['query']);
    $search_results = ai_summary_get_search_results($query);
    $summary = ai_summary_generate_summary($search_results);
    wp_send_json_success(array('summary' => $summary));
}
add_action('wp_ajax_ai_summary_search_bar', 'ai_summary_search_bar_ajax');
add_action('wp_ajax_nopriv_ai_summary_search_bar', 'ai_summary_search_bar_ajax');

// Function to get search results
function ai_summary_get_search_results($query) {
    $args = array(
        's' => $query,
        'posts_per_page' => 10,
    );
    $search_query = new WP_Query($args);
    $results = array();

    if ($search_query->have_posts()) {
        while ($search_query->have_posts()) {
            $search_query->the_post();
            $results[] = array(
                'title' => get_the_title(),
                'content' => get_the_excerpt()
            );
        }
    }
    wp_reset_postdata();
    return $results;
}

// Function to generate summary using Hugging Face API
function ai_summary_generate_summary($search_results) {
    $content = '';
    foreach ($search_results as $result) {
        $content .= $result['title'] . "\n" . $result['content'] . "\n\n";
    }

    $api_key = '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';  // Your Hugging Face API key
    $url = 'https://api-inference.huggingface.co/models/facebook/bart-large-cnn';
    $data = array(
        'inputs' => $content,
    );

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer $api_key\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE) {
        return 'Error generating summary';
    }

    $response = json_decode($result, true);
    return $response[0]['summary_text'];
}
?>