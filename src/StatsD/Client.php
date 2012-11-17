<?php

/*
 * This file is part of the Silex framework.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace StatsD;

use Exception;

class Client
{
    /**
     * @var array
     */
    protected static $config;

    /**
     *
     * @param array $config
     */
    public function __construct(array $config = array())
    {
        if (empty(self::$config)) {
            self::$config = array();
        }

        if (!empty($config)) {
            $this->setConfig($config);
        }
    }

    /**
     *
     * @param array $config
     * @return \StatsD\Client
     */
    public function setConfig(array $config)
    {
        self::$config = array_merge(self::$config, $config);

        return $this;
    }

    /**
     * Sets one or more timing values
     *
     * @param string|array $stats The metric(s) to set.
     * @param float $time The elapsed time (ms) to log
     */
    public function timing($stats, $time)
    {
        $this->updateStats($stats, $time, 1, "ms");
    }

    /**
     * Sets one or more gauges to a value
     *
     * @param string|array $stats The metric(s) to set.
     * @param float $value The value for the stats.
     */
    public function gauge($stats, $value)
    {
        $this->updateStats($stats, $value, 1, "g");
    }

    /**
     * A "Set" is a count of unique events.
     * This data type acts like a counter, but supports counting
     * of unique occurences of values between flushes. The backend
     * receives the number of unique events that happened since
     * the last flush.
     *
     * The reference use case involved tracking the number of active
     * and logged in users by sending the current userId of a user
     * with each request with a key of "uniques" (or similar).
     *
     * @param string|array $stats The metric(s) to set.
     * @param float $value The value for the stats.
     */
    public function set($stats, $value)
    {
        $this->updateStats($stats, $value, 1, "s");
    }

    /**
     * Increments one or more stats counters
     *
     * @param string|array $stats The metric(s) to increment.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function increment($stats, $sampleRate = 1)
    {
        $this->updateStats($stats, 1, $sampleRate, "c");
    }

    /**
     * Decrements one or more stats counters.
     *
     * @param string|array $stats The metric(s) to decrement.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @return boolean
     */
    public function decrement($stats, $sampleRate = 1)
    {
        $this->updateStats($stats, -1, $sampleRate, "c");
    }

    /**
     * Updates one or more stats.
     *
     * @param string|array $stats The metric(s) to update. Should be either a string or array of metrics.
     * @param int|1 $delta The amount to increment/decrement each metric by.
     * @param float|1 $sampleRate the rate (0-1) for sampling.
     * @param string|c $metric The metric type ("c" for count, "ms" for timing, "g" for gauge, "s" for set)
     * @return boolean
     */
    public function updateStats($stats, $delta = 1, $sampleRate = 1, $metric = "c")
    {
        if (!is_array($stats)) {
            $stats = array($stats);
        }

        $data = array();
        
        foreach ($stats as $stat) {
            $data[$stat] = sprintf("%d|%s", $delta, $metric);
        }

        $this->send($data, $sampleRate);
    }

    /*
     * Squirt the metrics over UDP
     */
    public function send($data, $sampleRate = 1)
    {
        if (isset($config["enabled"]) && !$config["enabled"]) {
            return;
        }

        if (!isset(self::$config["host"]) && !isset(self::$config["port"])) {
            return;
        }

        // sampling
        $sampledData = array();

        if ($sampleRate < 1) {
            foreach ($data as $stat => $value) {
                if ((mt_rand() / mt_getrandmax()) <= $sampleRate) {
                    $sampledData[$stat] = sprintf("%s|@%s", $value, $sampleRate);
                }
            }
        } else {
            $sampledData = $data;
        }

        if (empty($sampledData)) {
            return;
        }

        // Wrap this in a try/catch - failures in any of this should be silently ignored
        try {

            $fp = fsockopen(
                "udp://" . self::$config["host"], 
                self::$config["port"], 
                $errno, 
                $errstr
            );

            if (!$fp) {
                return;
            }

            foreach ($sampledData as $stat => $value) {
                fwrite($fp, sprintf("%s:%s", $stat, $value));
            }

            fclose($fp);

        } catch (Exception $e) {
        }
    }
}