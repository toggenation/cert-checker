<?php

namespace Toggenation;

// don't need this as I'm running it by composer
// require __DIR__ . '/../vendor/autoload.php';

use DateTime;
use Exception;
use Toggenation\Mailer;
use Dotenv\Dotenv;
use Composer\Script\Event;

class CertChecker
{
    /**
     * @param \Composer\Script\Event $event 
     * @param array $domains 
     * @return void 
     */
    public static function execute(Event $event, array $domains = [])
    {
        self::loadEnv();

        $config = require(CONFIG . 'config.php');

        $days = self::parseArgs($event->getArguments(), $config['days']);

        $domains = self::getUrls($config['urls']);

        foreach ($domains as $url) {
            $dateTo =  self::checkDomain($url);

            $daysTillExpiry = self::dateDiff(new DateTime(), $dateTo);

            self::log(message: "Checking {$url} has over {$days} days until expiry");

            if ($daysTillExpiry < $days) {
                (new Mailer())->send($daysTillExpiry, $url);
            };
        }
    }

    private static function log($message, int $priority = LOG_INFO)
    {
        echo $message . "\n";
        $ret = syslog($priority,  $message);
        var_dump($ret);
    }

    private static function loadEnv()
    {
        define('ROOT', dirname(dirname(__FILE__)) . '/');
        define('CONFIG', dirname(dirname(__FILE__)) . '/config/');

        $dotenv = Dotenv::createImmutable(CONFIG);
        $dotenv->load();
    }

    private static function getUrls($urls)
    {
        return array_filter(array_map('trim', $urls));
    }

    private static function parseArgs($args, $days)
    {
        $days = $args[0] ?? $days;

        $days = (int) $days;

        if (!($days > 0)) {
            throw new Exception("Must specify a valid days argument");
        }

        return $days;
    }

    private static function dateDiff(DateTime $start, DateTime $end): int
    {
        $dateInterval =  ($start)->diff($end);

        // if days are negative return a negative signed value
        return $dateInterval->invert ? -$dateInterval->days : $dateInterval->days;
    }

    private static function checkDomain(string $url): DateTime
    {
        $hostName = parse_url($url, PHP_URL_HOST);

        $get = stream_context_create(array("ssl" => array(
            // if expired it won't verify so allow for that
            'verify_peer' => false,
            "capture_peer_cert" => TRUE
        )));


        $read = stream_socket_client(
            "ssl://" . $hostName . ":443",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $get
        );

        $cert = stream_context_get_params($read);

        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        return DateTime::createFromFormat('U', $certinfo['validTo_time_t']);
    }
}