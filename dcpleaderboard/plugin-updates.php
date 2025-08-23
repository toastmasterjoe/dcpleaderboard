<?php

add_filter('pre_set_site_transient_update_plugins', 'dcpleaderboard_check_for_plugin_update');
function dcpleaderboard_check_for_plugin_update($transient) {
    
    error_log('dcpleaderboard_check_for_plugin_update>>'.print_r($transient, true));
    if (empty($transient->checked)) return $transient;

    $plugin_slug = plugin_basename(__FILE__);
    $remote = wp_remote_get('https://raw.githubusercontent.com/toastmasterjoe/dcpleaderboard/refs/heads/main/plugin-update.json');

    if (!is_wp_error($remote) && $remote['response']['code'] === 200) {
        $data = json_decode($remote['body']);
        if (version_compare($transient->checked[$plugin_slug], $data->version, '<')) {
            $transient->response[$plugin_slug] = (object) [
                'slug' => $data->slug,
                'new_version' => $data->version,
                'package' => $data->download_url,
                'url' => $data->homepage ?? ''
            ];
        }
    }

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

add_filter('auto_update_plugin', function($update, $item) {
    error_log($item->slug);
    return ($item->slug === 'dcpleaderboard') ? true : $update;
}, 10, 2);


?>