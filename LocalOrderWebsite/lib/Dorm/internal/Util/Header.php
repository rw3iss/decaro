<?php

namespace Dorm\Util;

class Header {

	public static function notFoundError() {
		header("HTTP/1.0 404 Not Found");
	}

  /**
   * Handles setting pages that are always to be revalidated for freshness by any cache.
   *
   * @param int $last_modified Timestamp in seconds
   */
  public static function exitIfNotModifiedSince($last_modified)
  {
    if (self::isModified($last_modified)) {
      self::sendNotModified();
      exit(0);
    }

    $last_modified = gmdate('D, d M Y H:i:s', $last_modified) . ' GMT';
    header("Cache-Control: must-revalidate");
    header("Last Modified: $last_modified");
  }
 
  /**
   * If you want to allow a page to be cached by shared proxies for one minute.
   *
   * @param int $seconds Interval in seconds
   */
  public static function cacheNoValidate($seconds = 60)
  {
    $now    = time();
    $lmtime = gmdate('D, d M Y H:i:s', $now) . ' GMT';
    $extime = gmdate('D, d M Y H:i:s', $now + $seconds) . 'GMT';
    // backwards compatibility for HTTP/1.0 clients
    header("Last Modified: $lmtime");
    header("Expires: $extime");
    // HTTP/1.1 support
    header("Cache-Control: public,max-age=$seconds");
  }

  /**
   * If instead you have a page that has personalization on it
   * (say, for example, the splash page contains local news as well),
   * you can set a copy to be cached only by the browser.
   *
   * @param int $seconds Interval in seconds
   */
  public static function cacheBrowser($seconds = 60)
  {
    $now    = time();
    $lmtime = gmdate('D, d M Y H:i:s', $now) . ' GMT';
    $extime = gmdate('D, d M Y H:i:s', $now + $seconds) . ' GMT';
    // backwards compatibility for HTTP/1.0 clients
    header("Last Modified: $lmtime");
    header("Expires: $extime");
    // HTTP/1.1 support
    header("Cache-Control: private,max-age=$seconds,s-maxage=0");
  }


  /**
   * If you want to try as hard as possible to keep a page from being cached anywhere.
   */
  public static function cacheNone()
  {
    // backwards compatibility for HTTP/1.0 clients
    header("Expires: 0");
    header("Pragma: no-cache");
    // HTTP/1.1 support
    header("Cache-Control: no-cache,no-store,max-age=0,s-maxage=0,must-revalidate");
  }

}

?>