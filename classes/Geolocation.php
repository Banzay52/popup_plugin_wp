<?php


namespace Sf\Popup;


class Geolocation
{
    const DEFAULT_COUNTRY = 'US';
    const DEFAULT_CONTINENT = 'NA';
    const DEFAULT_IP = '127.0.0.1';
    const REMOTE_API_URL = 'http://ip-api.com/json/';
    private $country_code;
    private $continent_code;
    private $ip_addr;

    public function __construct(string $ip_addr = '')
    {
        if ( $ip_addr === '' ) {
            $ip_addr = $_SERVER['REMOTE_ADDR'];
        }
        $this->setIpAddr($ip_addr);
        $this->setLocationData();
    }

    /**
     * @param mixed $ip_addr
     */
    private function setIpAddr(string $ip_addr): void
    {
        $this->ip_addr = $this->validateIp($ip_addr);

    }

    /**
     * @return mixed
     */
    public function getIpAddr()
    {
        return $this->ip_addr;
    }
    private function validateIp(string $ip_addr)
    {   if ( rest_is_ip_address($ip_addr) ) {
            if ($ip_addr === filter_var($ip_addr, FILTER_VALIDATE_IP,
                    FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip_addr;
            }
        }
        return false;
    }

    private function setCountryCode($countryCode = null)
    {
        $this->country_code = $countryCode;
    }

    private function setContinentCode($continentCode = null)
    {
        $this->continent_code = $continentCode;
    }

    public function getCountryCode()
    {
        return $this->country_code;
    }

    public function getContinentCode()
    {
        return $this->continent_code;
    }

    private function setLocationData() {
        $location_data = false;
        if ( $ip_addr = $this->getIpAddr() ) {
            $url = add_query_arg( ['fields' => 'status,countryCode,continentCode'], $this::REMOTE_API_URL . $ip_addr );
            $location_data = json_decode(file_get_contents($url), true);
        }
        if ( isset( $location_data['status'], $location_data['countryCode'] ) && $location_data['status'] === 'success' ) {
            $this->setContinentCode($location_data['continentCode']);
            $this->setCountryCode($location_data['countryCode']);
        } else {
            $this->setContinentCode();
            $this->setCountryCode();
        }
    }

    public function isCountryMatch(array $country_codes)
    {
        return count($country_codes) ? in_array($this->getCountryCode(), $country_codes) : true;
    }

    public function isContinentMatch(array $continent_codes)
    {
        return count($continent_codes) ? in_array($this->getContinentCode(), $continent_codes) : true;
    }
}