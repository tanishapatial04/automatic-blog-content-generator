<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_abcg_generate_blog_content', 'abcg_generate_blog_content_callback');

function abcg_generate_blog_content_callback() {
    // Check if the user has an API key set, redirect if not
    $user_api_key = get_option('abcg_api_key');
    if (!$user_api_key) {
        wp_send_json_error([
            'message' => 'API key is missing. Please add an API key in the plugin settings.'
        ]);
        exit;
    }

    // Verify nonce for security
    check_ajax_referer('abcg_nonce', 'nonce');

    // Get and sanitize input
    $title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : '';
    
    // Your fancy prompt
$prompt = 
    "Create a comprehensive blog post about \"{$title}\" using the following EXACT structure:\n\n" .
    "<p>[Write a compelling paragraph introduction that hooks the reader and explains why this topic matters]</p>\n\n" .
    "<h3>1. [Key Points based on the title]</h3>\n" .
    "<p>[Explanation paragraph]</p>\n" .
    "<p><strong>Example:</strong> [Practical example or case study]</p>\n\n" .
    "<h2>Conclusion</h2>\n" .
    "<p>[2-3 paragraph summary of key takeaways and final thoughts]</p>\n\n" .
    "<h2>Frequently Asked Questions</h2>\n" .
    "<h3>1. [Common Question 1]</h3>\n" .
    "<p>[Detailed answer]</p>\n\n" .
    "<h3>2. [Common Question 2]</h3>\n" .
    "<p>[Detailed answer]</p>\n\n" .
    "<h3>3. [Common Question 3]</h3>\n" .
    "<p>[Detailed answer]</p>\n\n" .
    "Important:\n" .
    "- Use EXACTLY the HTML tags specified (h2, h3, p)\n" .
    "- Include main points in the content section\n" .
    "- Each main point must have an example\n" .
    "- Write complete paragraphs\n" .
    "- FAQ answers should be thorough";

    // Prepare API request for Google Gemini
    $body = json_encode([ 
        "contents" => [[
            "parts" => [["text" => $prompt]]
        ]]
    ]);

    // Use the user's API key from the settings
    $response = wp_remote_post(
        'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=' . $user_api_key,
        [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $body,
            'timeout' => 60
        ]
    );

    // Handle response
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $body = json_decode(wp_remote_retrieve_body($response), true);

        // Gemini returns text in this path
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $generated_text = $body['candidates'][0]['content']['parts'][0]['text'];

            // Remove the title if it appears in the content
            $generated_text = str_ireplace($title, '', $generated_text); // Case-insensitive removal of title

            // Clean and balance HTML
            $html_content = force_balance_tags($generated_text);
            $html_content = wp_kses_post($html_content);
            $html_content = preg_replace('/(<\/h2>|<\/h3>|<\/p>)/', "$1\n\n", $html_content);

            wp_send_json_success($html_content);
        } else {
            wp_send_json_error(['message' => 'Failed to generate content.']);
        }
    } else {
        wp_send_json_error(['message' => 'Failed to connect to the API. Please try again later.']);
    }
}
