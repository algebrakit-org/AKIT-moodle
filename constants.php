<?php
global $AK_API_URL, $AK_CDN_URL, $AK_PROXY_URL;

// $AK_API_URL = 'http://localhost:3000';
// $AK_CDN_URL = 'http://localhost:4000/akit-widgets.js';
// $AK_PROXY_URL = 'http://localhost:3015/algebrakit-secure';

$region = get_config('qtype_algebrakit', 'region');
switch($region) {
    case 'eu': 
        $AK_API_URL = 'https://api.algebrakit.com';
        $AK_CDN_URL = 'https://widgets.algebrakit.com/akit-widgets.min.js';
        $AK_PROXY_URL = 'https://testbench.algebrakit.com/algebrakit-secure';
        break;

    case 'sg':
        $AK_API_URL = 'https://prod.api.sg-1.algebrakit.com';
        $AK_CDN_URL = 'https://prod.widgets.sg-1.algebrakit.com/akit-widgets.min.js';
        $AK_PROXY_URL = 'https://testbench.sg-1.algebrakit.com/algebrakit-secure';
        break;
}

