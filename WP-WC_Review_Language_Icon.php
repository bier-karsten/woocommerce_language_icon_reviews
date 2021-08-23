<?php
/**
 * Plugin Name: Language Icon for Reviews
 * Description: Display the country of a review's author behind his name as a small country-icon.
 * Version: 1.3.2
 * Author: Bier-Karsten
 * Author URI: https://github.com/bier-karsten
 * Developer: Bier-Karsten
 * Developer URI: https://github.com/bier-karsten
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 *
 * Woo: 12345:342928dfsfhsf8429842374wdf4234sfd
 * WC requires at least: 2.2
 * WC tested up to: 2.3
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function get_info_from_ip($ip = NULL, $fetch = "location", $deep_detect = TRUE)
{
    $output = NULL;
    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }

    // was soll geschehen?
    $fetch = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($fetch)));
    $support = array("country", "countrycode", "state", "region", "city", "location", "address");
    $continents = array(
        "EU" => "Europa",
        "OC" => "Australien (Ozeanien)",
        "NA" => "Nord Amerika",
        "SA" => "Süd Amerika",
        "AF" => "Afrika",
        "AS" => "Asien",
        "AN" => "Antarktis"
    );

    if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($fetch, $support)) {
        $ip_information = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
        // validate Country Code
        if (@strlen(trim($ip_information->geoplugin_countryCode)) == 2) {
            switch ($fetch) {
                case "location":
                    $output = array(
                        "city" => @$ip_information->geoplugin_city,
                        "state" => @$ip_information->geoplugin_regionName,
                        "country" => @$ip_information->geoplugin_countryName,
                        "country_code" => @$ip_information->geoplugin_countryCode,
                        "continent" => @$continents[strtoupper($ip_information->geoplugin_continentCode)],
                        "continent_code" => @$ip_information->geoplugin_continentCode,
                        "currency_symbol" => @$ip_information->geoplugin_currencySymbol,
                        "currency_code" => @$ip_information->geoplugin_currencyCode,
                    );
                    break;
                case "address":
                    $address = array($ip_information->geoplugin_countryName);
                    if (@strlen($ip_information->geoplugin_regionName) >= 1)
                        $address[] = $ip_information->geoplugin_regionName;
                    if (@strlen($ip_information->geoplugin_city) >= 1)
                        $address[] = $ip_information->geoplugin_city;
                    $output = implode(", ", array_reverse($address));
                    break;
                case "city":
                    $output = @$ip_information->geoplugin_city;
                    break;
                case "state":
                    $output = @$ip_information->geoplugin_regionName;
                    break;
                case "region":
                    $output = @$ip_information->geoplugin_regionName;
                    break;
                case "country":
                    $output = @$ip_information->geoplugin_countryName;
                    break;
                case "countrycode":
                    $output = @$ip_information->geoplugin_countryCode;
                    break;

                case "currency":
                    $output = array(
                        "currency_symbol" => @$ip_information->geoplugin_currencySymbol,
                        "currency_code" => @$ip_information->geoplugin_currencyCode,
                    );
            }
        }
    }
    return $output;
}

function custom_author($author, $comment_ID = 0)
{
    $comment = get_comment($comment_ID);
    // $author2 = get_comment_author($comment);

    $country_code = get_info_from_ip($comment->comment_author_IP, "Country Code");
    //echo '<img src="https://www.countryflags.io/' . $country_code . '/flat/32.png" alt="' . $country_code . '"/>';
    // echo $author2;
    echo $author . '<img src="https://www.countryflags.io/' . $country_code . '/flat/16.png" alt="' . $country_code . '"/>';
}


add_action('woocommerce_review_before_comment_meta', function ($comment) {
    add_filter('comment_author', 'custom_author', $author, $comment);

});

add_action('woocommerce_review_after_comment_meta', function ($comment) {
    remove_filter('custom_author', 'comment_author', 10);
});
