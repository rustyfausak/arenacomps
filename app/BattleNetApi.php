<?php

namespace App;

class BattleNetApi
{
    /**
     * The bnet configuration as defined in `config/bnet.php`.
     *
     * @var array
     */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function call($region, $endpoint, $params)
    {
        if (!array_key_exists('apikey', $this->config) || !$this->config['apikey']) {
            throw new \Exception("Missing " . __CLASS__ . " config 'apikey'.");
        }
        $params['apikey'] = $this->config['apikey'];
        $url = "https://{$region}.api.battle.net{$endpoint}?";
        foreach ($params as $k => $v) {
            $url .= "&{$k}=" . urlencode($v);
        }
        print $url . "\n";
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
