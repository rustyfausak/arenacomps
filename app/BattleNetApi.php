<?php

namespace App;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

class BattleNetApi
{
    /**
     * The bnet configuration as defined in `config/bnet.php`.
     *
     * @var array
     */
    protected $config;

    /**
     * The Guzzle client for making requests.
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->client = null;
    }

    /**
     * Make a HTTP request and return the response body. Throws an exception on error.
     *
     * @param string $region    The region, like 'us' or 'eu'.
     * @param string $endpoint  API endpoint, like '/wow/leaderboard/3v3'.
     * @param array  $params    Array of parameters, like ['locale' => 'en_US'].
     * @return string
     * @throws \Exception
     */
    public function call($region, $endpoint, $params)
    {
        if (!array_key_exists('apikey', $this->config) || !$this->config['apikey']) {
            throw new \Exception("Missing " . __CLASS__ . " config 'apikey'.");
        }
        if (!$this->client) {
            $this->client = new Client([
                'timeout' => 120,
                'verify' => false,
            ]);
        }
        $params['apikey'] = $this->config['apikey'];
        $url = "https://{$region}.api.battle.net{$endpoint}";
        try {
            $response = $this->client->request('GET', $url, ['query' => $params]);
        }
        catch (RequestException $e) {
            throw new \Exception(__CLASS__ . '->call() ERROR: ' . $e->getMessage());
        }
        return $response->getBody();
    }

    /**
     * @param string $region
     * @param string $bracket
     * @param string $locale
     */
    public function getPvpLeaderboard($region, $bracket, $locale = null)
    {
        if (!$locale) {
            $locale = self::getDefaultLocaleForRegion($region);
        }
        return $this->call(
            $region,
            "/wow/leaderboard/{$bracket}",
            ['locale' => $locale]
        );
    }

    /**
     * @param string $region
     */
    public static function getDefaultLocaleForRegion($region)
    {
        switch ($region) {
            case 'us':
                return 'en_US';
            case 'eu':
                return 'en_GB';
        }
        return null;
    }
}
