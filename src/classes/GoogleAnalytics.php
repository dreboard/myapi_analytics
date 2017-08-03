<?php

namespace MyApiCore\System;

/**
 * Class GoogleAnalytics
 * Send uri and responses to google analytics
 *
 * @package     App\Components
 * @subpackage  Controllers
 * @since       v0.1.0
 *
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide#event
 * @see https://developers.google.com/analytics/devguides/collection/protocol/v1/parameters
 *
 */

/**
 * Class GoogleAnalytics
 * @package MyApiCore\System
 */
abstract class GoogleAnalytics
{

    private const TRACKING_ID = 'UA-58878056-1';

    private const GA_END_POINT = 'https://www.google-analytics.com/collect';

    /**
     * Generate UUID
     * @return string
     */
    protected static function gen_uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

	/**
	 * @return mixed
	 */
	protected static function gaParseCookie() {
		if (isset($_COOKIE['_ga'])) {
			list($version, $domainDepth, $cid1, $cid2) = explode('.', $_COOKIE["_ga"], 4);
			$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1 . '.' . $cid2);
			$cid = $contents['cid'];
		} else {
			$cid = self::gen_uuid();
		}
		return $cid;
	}
    /**
     * Send Data to Google Analytics
     *
     * @param $url
     * @param $user_agent
     * @return mixed
     * @internal param $data
     *
     */
    public static function gaSendData($url, $uip)
    {
        try {
            $data = array(
                'v' => 1,
                'tid' => self::TRACKING_ID,
                'cid' => self::gen_uuid(),
                't' => 'pageview'
            );

            $data['dl'] = $url;
            $data['ev'] = "34";
            $data['uip'] = $uip;
            $data['ua'] = 'HTTP API Agent';

            $content = http_build_query($data);
            $content = utf8_encode($content);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, $data['ua']);
	        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_URL, self::GA_END_POINT);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if (curl_exec($ch) === false) {
                $msg = curl_error($ch);
                throw new \Exception($msg);
            }
            curl_close($ch);
        } catch (\Throwable $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Send Pageview Function for Server-Side Google Analytics
     *
     * @param null $hostname
     * @param null $page
     * @param null $title
     */
    public static function gaSendPageview($hostname = null, $page = null, $title = null)
    {
        $data = array(
            'v' => 1,
            'tid' => self::TRACKING_ID,
            'cid' => self::gaParseCookie(),
            't' => 'pageview',
            'dh' => $hostname, //Document Hostname "site.com"
            'dp' => $page, //Page "/something"
            'dt' => $title //Title
        );
        self::gaSendData($data, '127.0.0.1');
    }

    /**
     * Send Event Function for Server-Side Google Analytics
     *
     * @param null $category
     * @param null $action
     * @param null $label
     */
    public static function gaSendEvent($category = null, $action = null, $label = null)
    {
        $data = array(
            'v' => 1,
            'tid' => self::TRACKING_ID,
            'cid' => self::gaParseCookie(),
            't' => 'event',
            'ec' => $category, //Category (Required)
            'ea' => $action, //Action (Required)
            'el' => $label //Label
        );
        self::gaSendData($data, '127.0.0.1');
    }
}