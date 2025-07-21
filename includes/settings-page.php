<?php
// Ensure WordPress environment
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function abcg_render_settings_page() {
    ?>
    <div class="wrap abcg-settings-page">
        <h1>AutoBlogCreation Settings</h1>
        <p>To use the AutoBlogCreation feature, you need to generate an API key from Google AI Studio. Follow the steps below to get your API key:</p>

        <div class="abcg-steps-container">
            <h3>Steps to Get Your Google AI API Key</h3>
            <div class="abcg-steps-box-container">
                <div class="abcg-step-box">
                    <div class="abcg-step-icon">
                        <i class="fa fa-cloud"></i>
                    </div>
                    <div class="abcg-step-content">
                        <strong>Step 1: Visit Google AI Studio</strong>
                        <p>Go to the <a href="https://cloud.google.com/ai" target="_blank">Google Cloud AI Studio</a> page.</p>
                    </div>
                </div>

                <div class="abcg-step-box">
                    <div class="abcg-step-icon">
                        <i class="fa fa-user-plus"></i>
                    </div>
                    <div class="abcg-step-content">
                        <strong>Step 2: Sign Up / Log In</strong>
                        <p>If you don't have an account, sign up for Google Cloud and create a new project.</p>
                    </div>
                </div>

                <div class="abcg-step-box">
                    <div class="abcg-step-icon">
                        <i class="fa fa-cogs"></i>
                    </div>
                    <div class="abcg-step-content">
                        <strong>Step 3: Enable the API</strong>
                        <p>Navigate to the <strong>APIs & Services</strong> dashboard, then enable the <strong>Generative Language API</strong> for your project.</p>
                    </div>
                </div>

                <div class="abcg-step-box">
                    <div class="abcg-step-icon">
                        <i class="fa fa-key"></i>
                    </div>
                    <div class="abcg-step-content">
                        <strong>Step 4: Generate API Key</strong>
                        <p>In the <strong>Credentials</strong> section, click on <strong>Create Credentials</strong> and select <strong>API Key</strong>.</p>
                    </div>
                </div>

                <div class="abcg-step-box">
                    <div class="abcg-step-icon">
                        <i class="fa fa-paste"></i>
                    </div>
                    <div class="abcg-step-content">
                        <strong>Step 5: Paste the API Key</strong>
                        <p>Copy the generated API key and paste it in the input field below.</p>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="options.php">
            <?php
                settings_fields('abcg_settings_group');
                do_settings_sections('abcg-settings');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td class="flex">
                        <input type="text" name="abcg_api_key" id="abcg_api_key" value="<?php echo esc_attr(get_option('abcg_api_key')); ?>" class="regular-text" readonly />
                        <button type="button" id="edit-api-key" class="button">Edit API Key</button>
                        <button type="submit" name="update_api_key" id="update-api-key" class="button" style="display:none;">Update API Key</button>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        document.getElementById('edit-api-key').addEventListener('click', function() {
            var apiKeyField = document.getElementById('abcg_api_key');
            var updateButton = document.getElementById('update-api-key');
            
            apiKeyField.readOnly = false;
            updateButton.style.display = 'inline-block';
            apiKeyField.focus();
        });
    </script>
    <?php
}

// Sanitization callback
function abcg_sanitize_api_key($input) {
    // Example: sanitize text field by stripping tags and trimming spaces
    return sanitize_text_field(trim($input));
}

// Register the setting with sanitization
function abcg_register_settings() {
    register_setting(
        'abcg_settings_group', // Option group
        'abcg_api_key',        // Option name
        'abcg_sanitize_api_key' // Sanitization callback
    );
}
add_action('admin_init', 'abcg_register_settings');

// Save the API Key when the form is submitted
function abcg_save_api_key() {
    if (
        isset($_POST['update_api_key']) &&
        isset($_POST['abcg_save_api_key_nonce'])
    ) {
        // Unslash and sanitize nonce before verification
        $nonce = sanitize_text_field(wp_unslash($_POST['abcg_save_api_key_nonce']));

        if (wp_verify_nonce($nonce, 'abcg_save_api_key_action')) {
            // Unslash and sanitize API key input
            $api_key = isset($_POST['abcg_api_key']) ? sanitize_text_field(wp_unslash($_POST['abcg_api_key'])) : '';

            update_option('abcg_api_key', $api_key);
        }
    }
}
add_action('admin_post_update_api_key', 'abcg_save_api_key');
