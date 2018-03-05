<?php

namespace Affiliate;

use app\lib\helpers\Curl;
use InvalidArgumentException;
use Affiliate\Exceptions\MissingFieldException;

class Rakuten
{
    const BASE_API_URL = 'https://api.rakutenmarketing.com';
    const API_NAME     = '';
    const API_VERSION  = '1.0';

    const TOKEN_END_POINT = '/token';
    const TOKEN_HEADER = 'a3NQelhIWEd6TERXME85NHNqeDdGcHpYZW1nYTpNTTNkM3YzdlZ4eUk1T0o5c0xIcDRLZlpxN1Fh';

    const HEADER_TYPE_BASIC  = 'Basic';
    const HEADER_TYPE_BEARER = 'Bearer';

    public $tokenFile ='/access_token';
    /**
     * Rakuten grant type to get API token
     *
     * ['password', 'refresh_token']
     * @var $grant
     * @type String
     */
    public $grant;
    /**
     * Rakuten username to get API token
     *
     * @var $username
     * @type String
     */
    public $username;
    /**
     * Rakuten password to get API token
     *
     * @var $password
     * @type String
     */
    public $password;
    /**
     * Rakuten scope to get API token
     *
     * @var $scope
     * @type String
     */
    public $scope;

    /**
     * API ACCESS TOKEN
     * @var $accessToken
     */
    protected $accessToken;

    public function __construct($options = [])
    {
        if (empty($options['grant_type']) || $options['grant_type'] === false) {
            throw new MissingFieldException('grant_type');
        }

        if (empty($options['username'])) {
            throw new MissingFieldException('username');
        }

        if (empty($options['password']) || $options['password'] === false) {
            throw new MissingFieldException('password');
        }

        if (empty($options['scope']) || $options['scope'] === false) {
            throw new MissingFieldException('scope');
        }

        $this->grant    = $options['grant_type'];
        $this->username = $options['username'];
        $this->password = $options['password'];
        $this->scope    = $options['scope'];
    }

    /**
     * Check Token Of Expire or Not
     * Then Have it Refreshed.
     *
     * @return $token
     */
    public function refreshToken() {}

    /**
     * Get Access Token From A text file
     * @return mixed|null
     */
    public function getToken()
    {
        $accessToken = null;
        if (file_exists($_SERVER['DOCUMENT_ROOT'].$this->tokenFile)) {
            $accessToken = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT'].$this->tokenFile));
        }

        return $accessToken;
    }

    /**
     * Save Access Token to a text file
     *
     * @param $accessToken
     */
    public function saveAccessToken($accessToken)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].$this->tokenFile, serialize($accessToken));
    }

    /**
     * Rakuten Make Request To API
     */
    public function loadAccessToken()
    {
        $header[] = 'Authorization: '. self::HEADER_TYPE_BASIC. ' '. self::TOKEN_HEADER;

        $curl  = new Curl;

        $token = $curl->post(self::BASE_API_URL.self::TOKEN_END_POINT, [
            'grant_type' => $this->grant,
            'username' => $this->username,
            'password' => $this->password,
            'scope' => $this->scope
        ], $header);

        $this->saveAccessToken($token);
    }
}