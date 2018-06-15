<?php

namespace Affiliate;

use app\lib\helpers\Curl;
use Affiliate\Exceptions\MissingFieldException;

class Experience
{
    const AUTH_TYPE_BASIC = 'basic';
    const BASE_API_URL = 'https://sportspass.ticketmates.net/en/api/v2/things-to-do/hot-deals';

    /**
     * @var $api_url
     * Subdomain of the API
     */
    public  $api_url;

    public  $header;

    /**
     * @var $_username
     */
    private $_username;

    /**
     * @var $_password
     */
    private $_password;

    public function __construct($options = [])
    {
        if (empty($options['api_url']))
            throw new MissingFieldException('api_url');

        if (empty($options['username']))
            throw new MissingFieldException('username');

        if (empty($options['password']))
            throw new MissingFieldException('password');

        $this->setApiUrl($options['api_url']);
        $this->setUserName($options['username']);
        $this->setPassword($options['password']);
    }

    /**
     * @param $url
     */
    public function setApiUrl($url)
    {
        $this->api_url = $url;
    }

    /**
     * @param $username
     */
    public function setUserName($username)
    {
        $this->_username = $username;
    }

    /**
     * @param $password
     */
    public function setPassword($password)
    {
        $this->_password = $password;
    }

    /**
     * Set Header Request
     */
    public function setHeader()
    {

        $this->header = $this->getUserName() . ':' . $this->getPassword();
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return mixed
     */
    public function getApiUrl()
    {
        return $this->api_url;
    }

    /**
     * @return mixed
     */
    public function getUserName()
    {
        return $this->_username;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Get Experience Oz HotDeals
     * @return string
     */
    public function hotDeals()
    {
        $curl = new Curl;
        $this->setHeader();
        $curl->setAuthType(self::AUTH_TYPE_BASIC);

        $response = $curl->get(self::BASE_API_URL,  '', $this->getHeader());

        return $response;
    }
}