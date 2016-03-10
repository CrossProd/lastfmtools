<?php

//
// FILESYS
//

define('ABSOLUTE_PATH', '/home/lastfmtool/public_html/');

//
// WEB
//

define('BASE_HREF', 'http://lastfmtools.com/web/');
define('BASE_SITE', 'http://lastfmtools.com/');


//
// FILESYS
//

$GLOBALS['absolute_path'] = '/home/lastfmtool/public_html/';

//
// WEB
//

$GLOBALS['base_href'] = 'http://lastfmtools.com/web/';

//
// FILE CACHE
//

$GLOBALS['clientcache_enabled'] = true;
$GLOBALS['filecache_enabled']   = true;

$GLOBALS['filecache_user_7day']    = 3 * 24 * 3600;	 // 3 day
$GLOBALS['filecache_user_1month']  = 7 * 24 * 3600;	 // 7 days
$GLOBALS['filecache_user_3month']  = 7 * 24 * 3600;	 // 7 days
$GLOBALS['filecache_user_6month']  = 10 * 24 * 3600;	 // 10 days
$GLOBALS['filecache_user_12month'] = 15 * 24 * 3600;	 // 15 days
$GLOBALS['filecache_user_overall'] = 20 * 24 * 3600;	 // 20 days

$GLOBALS['priority_filecache_user_7day']    =  1 * 24 * 3600;	 // 1 day
$GLOBALS['priority_filecache_user_1month']  =  2 * 24 * 3600;	 // 2 days
$GLOBALS['priority_filecache_user_3month']  =  2 * 24 * 3600;	 // 2 days
$GLOBALS['priority_filecache_user_6month']  =  3 * 24 * 3600;	 // 3 days
$GLOBALS['priority_filecache_user_12month'] =  3 * 24 * 3600;	 // 3 days
$GLOBALS['priority_filecache_user_overall'] =  5 * 24 * 3600;	 // 5 days

define('IMAGE_CACHE_ALBUM_ART_MAX_AGE', 90 * 24 * 3600);
define('IMAGE_CACHE_ARTIST_ART_MAX_AGE', 30 * 24 * 3600);

define('IMAGE_CACHE_ALBUMART_PATH', ABSOLUTE_PATH . 'new_cache/downloaded/album_art/');
define('IMAGE_CACHE_ARTISTART_PATH', ABSOLUTE_PATH . 'new_cache/downloaded/artist_art/');

$GLOBALS['filecache_system_albumart']  = 30 * 24 * 60; // 30 days
$GLOBALS['filecache_system_artistart'] = 30 * 24 * 60; // 30 days

$GLOBALS['filecache_system_albumart_original_path'] = 'cache/downloaded/album_art/original/';
$GLOBALS['filecache_system_albumart_100x100_path']  = 'cache/downloaded/album_art/100x100/';
$GLOBALS['filecache_system_albumart_85x85_path']    = 'cache/downloaded/album_art/85x85/';
$GLOBALS['filecache_system_albumart_75x75_path']    = 'cache/downloaded/album_art/75x75/';
$GLOBALS['filecache_system_albumart_67x67_path']    = 'cache/downloaded/album_art/67x67/';
$GLOBALS['filecache_system_albumart_60x60_path']    = 'cache/downloaded/album_art/60x60/';
$GLOBALS['filecache_system_albumart_50x50_path']    = 'cache/downloaded/album_art/50x50/';

$GLOBALS['filecache_system_artistart_original_path'] = 'cache/downloaded/artist_art/original/';
$GLOBALS['filecache_system_artistart_100x100_path']  = 'cache/downloaded/artist_art/100x100/';
$GLOBALS['filecache_system_artistart_75x75_path']    = 'cache/downloaded/artist_art/75x75/';
$GLOBALS['filecache_system_artistart_67x67_path']    = 'cache/downloaded/artist_art/67x67/';
$GLOBALS['filecache_system_artistart_60x60_path']    = 'cache/downloaded/artist_art/60x60/';
$GLOBALS['filecache_system_artistart_50x50_path']    = 'cache/downloaded/artist_art/50x50/';

//
// DB CACHE
//

$GLOBALS['caching_db_host'] = 'localhost';
$GLOBALS['caching_db_name'] = 'lastfm_main';
$GLOBALS['caching_db_user'] = 'lastfm_main';
$GLOBALS['caching_db_pass'] = '';

define('LASTFM_DB_HOST', 'localhost');
define('LASTFM_DB_NAME', 'lastfm_main');
define('LASTFM_DB_USER', 'lastfm_main');
define('LASTFM_DB_PASSWORD', '');

$GLOBALS['cache_artist_toptags'] = 28 * 24 * 60;			// 4 weeks
$GLOBALS['cache_artist_similar'] = 28 * 24 * 60;			// 4 weeks

$GLOBALS['cache_user_topartists']        = 	 1 * 12 * 60;	// 12 hours
$GLOBALS['cache_user_topalbums']         =   1 * 12 * 60;	// 12 hours
$GLOBALS['cache_user_weeklychartlist']   =   1 * 24 * 60;	//  1 day
$GLOBALS['cache_user_weeklyartistchart'] =  28 * 24 * 60;	//  4 weeks
$GLOBALS['cache_user_weeklyalbumchart']  =  28 * 24 * 60;	//  4 weeks

$GLOBALS['cache_album_info']  =  28 * 24 * 60;	//  4 weeks

//
// LAST.FM SERVICES
//

$GLOBALS['lastfm_host']   = 'ws.audioscrobbler.com';
$GLOBALS['lastfm_port']   = '80';
$GLOBALS['lastfm_key']    = '';
$GLOBALS['lastfm_secret'] = '';

define('LASTFM_API_HOST', 'ws.audioscrobbler.com');
define('LASTFM_API_PORT', '80');
define('LASTFM_API_KEY', '');
define('LASTFM_API_SECRET', '');

//
// TWITTER KEYS
//

define('TWITTER_API_KEY', '');
define('TWITTER_API_SECRET', '');
define('TWITTER_API_CALLBACK', 'http://lastfmtools.com/web/twittercallback.php');

//
// AVAILABLE GENERATORS
//

$GLOBALS['generator_list'] = array
(
	'topalbums_collage',
	'topartists_collage',
	'topartists_typewriter',
	'topartists_spiral',
	'topalbums_collage_var01',
	'topartists_collage_var01',
);
