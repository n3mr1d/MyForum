<?php
if (!defined('IN_MYBB')) {
    die('Direct initialization not allowed.');
}

$plugins->add_hook("global_start", "tor_sock");

function tor_sock_info()
{
    return [
        "name"          => "Tor URL Override",
        "description"   => "Override bburl jika diakses via .onion",
        "website"       => "https://example.com",
        "author"        => "idrift",
        "version"       => "1.0",
        "compatibility" => "18*"
    ];
}

function tor_sock()
{
    global $mybb;

    if (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], '.onion') !== false) {
        if (!empty($mybb->settings['tor_onion'])) {
            $mybb->settings['bburl'] = rtrim($mybb->settings['tor_address'], '/');
        }
    }
}
