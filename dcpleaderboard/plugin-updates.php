<?php
add_filter('auto_update_plugin', function($update, $item) {
    return  ($item->id === 'dcpleaderboard/dcpleaderboard.php') ? true : $update;
}, 10, 2);

add_filter('pre_set_site_transient_update_plugins', 'dcpleaderboard_check_for_plugin_update');
function dcpleaderboard_check_for_plugin_update($transient) {

    $plugin_file = 'dcpleaderboard/dcpleaderboard.php';

    $remote = wp_remote_get('https://raw.githubusercontent.com/toastmasterjoe/dcpleaderboard/refs/heads/main/plugin-update.json');
    if (!is_wp_error($remote) && $remote['response']['code'] === 200) {
        $data = json_decode($remote['body']);
        error_log($transient->checked[$plugin_file]);
        if (version_compare($transient->checked[$plugin_file], $data->version, '<')) {
            $transient->response[$plugin_file] = (object)[
                'slug' => 'dcpleaderboard',
                'new_version' => $data->version,
                'package' => $data->download_url,
                'url' => $data->homepage ?? ''
            ];
        }
    }
    error_log(print_r($transient,true));
    return $transient;
}

add_filter('plugins_api', 'dcpleaderboard_plugin_info', 20, 3);
function dcpleaderboard_plugin_info($res, $action, $args) {
    if ($args->slug !== 'dcpleaderboard') return $res;

    $remote = wp_remote_get('https://raw.githubusercontent.com/toastmasterjoe/dcpleaderboard/refs/heads/main/plugin-update.json');
    if (!is_wp_error($remote) && $remote['response']['code'] === 200) {
        $data = json_decode($remote['body']);
        return (object) [
            'name' => $data->name,
            'slug' => $data->slug,
            'version' => $data->version,
            'author' => $data->author,
            'homepage' => $data->homepage,
            'sections' => $data->sections,
            'download_link' => $data->download_url
        ];
    }

    return $res;
}




?>