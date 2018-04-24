<?php

/**
 * Create separate cache files based on the cookie consent value.
 */
add_filter('supercache_filename_str', function ($string) {
    $cookie = $_COOKIE['cookieconsent_status'] ?? null;
    if ($cookie) {
        $string = $string . '-' . $cookie;
    }
    return $string;
});
