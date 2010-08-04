<?php
$suspicious_fingerprints = array(
// Empty
'd41d8cd98f00b204e9800998ecf8427e',
// Near empty fingerprints
'd87a5617df45f58730aa2412008966e9', // Accept:*/*
'd4ad31d6bbd2b13d7e9683b23b1f6680', // Mozilla/5.0
'8d4e52f445afc04479700fb94f606ea2', // Mozilla/5.0 - Accept:*/*
'd3c137acd8ada3e23b5d18a7773260ec', // Mozilla/4.0 - Accept:*/*
'd28f73e67a253fb8503538b92ff5f33f', // Mozilla/3.0 (compatible)
'a55dbce033e47342f4493b6b8e317a11', // Accept-Encoding:identity
'b8a1d0f6c953cb9ff2dca95425cf4a67', // Mozilla/4.0 (compatible)
'4c67cac030adba659c1ad691368db27e', // Mozilla/4.0 (compatible; ICS)
'e261d653a34451b1db31d6613aace1f7', // Mozilla/5.0 (compatible)
'e36cf8d82aaacacd2533bf7779f362bd', // Spider - Accept:*/*
// Programming languages defaults
'e92acd969162f34aaac2099466e58464', // Ruby/1.8.7
'023beda1cb2cb7eea339c13a304b8d22', // Python-urllib/2.5
'db71ede31df67309d22fba536bb786ae', // Python-urllib/2.6
'790f947b2a7fe78890c8fa9a1ab50749', // PycURL/7.18.2
'7501eb1d36df266c545527806e540e09', // The Incutio XML-RPC PHP Library
// Curl
'7b1b91d32d341e80e89ae21bb8f5554b', // curl/7.19.6 (i386-pc-win32) libcurl/7.19.6 OpenSSL/0.9.8k zlib/1.2.3
// Bots
'40fc775be2dcad9d6d0d798276a6177d', // Mozilla 5.0 (US - static.theplanet.com)
'd05580236d7563115237363dc740f983', // TREND MICRO
// Explorer
'201b89a0f2a212c9f6b73dab58ab9db3',    //  *  - Windows XP    - Explorer 6.0     (Default IE - User Agent only)
'21f73ced1ec3fc21bd9d74eb037ec189',    //  *  - Windows XP    - Explorer 6.0     (US - yahoo.com)  
'2a945eb820e0fb959e0eb42ebcae9c9f',    // LD  - Windows XP    - Explorer 8.0     (US - Vrtservers)
'314bebd09a5bf6aab9b54fd3fdf9ae80',    // LD  - Windows XP    - Explorer 6.0     (CN - 58.61.164.*|124.115.1.*|114.80.93.71)
'71769c0690c09ac31cc2bd7898a107a5',    // BM  - Windows XP    - Explorer 6.0     (US - websitewelcome.com|hostgator.com)
'c58abcb98a1260e3cb7de50b305b5cd1',    // BM  - Windows XP    - Explorer 6.0     (US - websitewelcome.com|hostgator.com)
'6b132ced76c43a575a702ac7917c2991',    // BM  - Windows XP    - Explorer 6.0     (TW - dynamic.hinet.net)
'4d12feadaaa0d2de366391b5f26d9723',    // BM  - Windows XP    - Explorer 6.0     (PERU)
'a83d11e2c4b78a31f922ac5e6535bd02',    // BM  - Windows XP    - Explorer 6.0     (CN - 61.135.162.*|61.135.169.*)
'407fa0dab1fc4043e1dd5d197b5d3b7c',    // BM  - Windows MC 6  - Explorer 8.0
'938331c1534578621896d7be51bc4de3',    // BM  - Windows XP    - Explorer 7.0
'b7449fcac8672fb618b33bbfe477981a',    // BM  - Windows XP    - Explorer 7.0     (US - 24.39.1.*|nys.biz.rr.com)
'd15670bbecb02aa913a84504cd8d3616',    // BM  - Windows XP    - Explorer 6.0
'a8d05109ce1452f434c41086c58763e3',    // BM  - Windows XP    - Explorer 6.0     (US - CALPOP)
'7b9e8b3e15f083ffd4003d6348a862b5',    // BM  - Windows XP    - Explorer 7.0     (FR - OVH)
'5a02b5cee84ff465518e063a71662b03',    // LD  - Windows 2000  - Explorer 5.01    (US - wp-signup.php)
'6ee31a0f6b63c451af5a393eae58ee98',    // BM  - Windows XP    - Netscape 7.2
// Firefox
'8310c5852ee8d17f39094a923bf537b8',    //  LD - MacOS X 10.5  - Firefox 3.6.3    (US - NatCoWeb Corp)
'4679192bb2f9d7a8a77c564a80f7dbd8',    //  LD - Windows Vista - Firefox 3.0.11   (US - comcastbusiness.net)
'93faf5eb2b3ef9321bb4269ba3f98940',    //  LD - Windows Vista - Firefox 3.5.3    (US - comcastbusiness.net)
'b64337e5058a34655a36e2c386e38b5a',    //  LD - Gentoo        - Firefox 3.5.6    (US - hostgator.com)
'6e0b261a641d3f6ad9c53f4f0bcff690',    //  LD - MacOS X       - Firefox 2.0.0.7  (US - amazonaws.com)
'2e4572613564df07f322e9d6411afd91',    //  BM - Windows XP    - Firefox 3.0.2    (CN - 110.75.169.*)
'ae21b88a5f9d9e0f194d197bf3c1b16a',    //  BM - Unknown       - Firefox 3.0.5    (KR - 211.43.152.*)
// BM - not identified
'3dfed844dc126275d6d535bd0128a037',    //  BM - Windows XP    - Firefox 3.0.5    (various)
'9f2c5a729cf256ae7df689a153233397',    //  BM - Windows XP    - Firefox 3.0.10   (various)
// BM - inferno.name
'a19471cdfd5262b77d9c2edb6ebc1510', // Windows 2003 - Opera 7.60
'b6a988533111b50e812790f44de838aa', // Windows XP   - Explorer 6.0
'c7345ab14c9c39e36737b503b6f11239', // Windows 2000 - Explorer 5.5
'1f5e448c64308bf6732cc9d2df4eb538', // Windows XP   - Explorer 7.0
'9da99f80922dc5c710dbc8602ea9ce83', // Windows 2003 - Explorer 6.0
'870660ecfc39667a3fd987a147c51170', // Windows 2000 - Explorer 5.5
'19b8d1b99b6dd00dbcb4251b013969d5', // Windows XP   - Explorer 6.0
);
return $suspicious_fingerprints;
?>