<?php
/*
Plugin Name: Bootstrap Link Plugin
Description: Adds a form with Bootstrap styling to submit a link.
Version: 2.3
Author: Quan Nguyen
*/

// Enqueue scripts and styles
function bootstrap_link_enqueue_scripts()
{
    wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap-link-script', plugins_url('js/bootstrap-script-v2.js', __FILE__), array('jquery'), null, true);

    // Pass AJAX parameters to script.js
    wp_localize_script('bootstrap-link-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

add_action('wp_enqueue_scripts', 'bootstrap_link_enqueue_scripts');

//Getlink Function

function getLink($url)
{
    // API endpoint
    $api_url = 'https://noads-api.quanna.dev/api/view?url=' . urlencode($url);

    // Make the API request
    $response = wp_remote_get($api_url);

    // Check for errors
    if (is_wp_error($response)) {
        return json_encode(array('error' => 'Error: ' . $response->get_error_message()));
    }

    // Retrieve the API response body
    $body = wp_remote_retrieve_body($response);

    // Decode the JSON response
    $decoded_body = json_decode($body, true);

    // Check if decoding was successful
    if ($decoded_body === null) {
        return json_encode(array('error' => 'Error decoding JSON response'));
    }

    return $decoded_body;
}


// Shortcode callback function
function bootstrap_link_form_shortcode()
{
    ob_start(); // Start output buffering
    ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form id="bootstrap-link-form" action="#" method="post">
                    <div class="form-group">
                        <label for="link_url">Enter your link URL:</label>
                        <input type="url" class="form-control" name="link_url" id="link_url"
                               placeholder="https://motchill.uk/xem-phim/cua-hang-sat-thu/tap-1-138757" required>
                        <?php wp_nonce_field('get_link_nonce', 'get_link_nonce'); ?>
                    </div>
                    <button type="submit" class="btn btn-primary">Get Link</button>
                </form>
                <div id="link-result" class="mt-3"></div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean(); // Return buffered content
}

add_shortcode('bootstrap_link_form', 'bootstrap_link_form_shortcode');

// AJAX callback function
function handle_bootstrap_link_request()
{
    //check_ajax_referer('bootstrap_link_nonce', 'nonce');

    $link_url = isset($_POST['link_url']) ? esc_url($_POST['link_url']) : '';

    $res = getLink($link_url);
    $titles = explode('|', $res['name']);
    //split title |
    $subtitle = $titles[1];
    $title = $titles[0];
    // Echo the unordered list of media links
    ?>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title"><?php echo esc_html($title); ?></h4>
            <h6 class="card-subtitle mb-2 text-muted"><?php echo esc_html($subtitle); ?></h6>

            <?php foreach ($res['media'] as $index => $mediaLink) : ?>
                <!-- Use JavaScript to check if the link is live -->
                <a class="card-link" href="<?php echo esc_url($mediaLink); ?>" target="_blank">View Link</a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php

    wp_die(); // This is required to terminate immediately and return a proper response
}


add_action('wp_ajax_nopriv_handle_bootstrap_link_request', 'handle_bootstrap_link_request');
add_action('wp_ajax_handle_bootstrap_link_request', 'handle_bootstrap_link_request');
