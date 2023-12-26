<?php

namespace todo;

class ToDo
{
    function formatUrl(string $url) { // use this to sanitize with middleware the user url input
        if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
            return $url;
        } elseif (strpos($url, 'www.') === 0) {
            return 'https://' . substr($url, 4);
        } else {
            return 'https://' . $url;
        }
    }
}