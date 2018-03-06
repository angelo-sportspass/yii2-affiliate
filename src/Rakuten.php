<?php

namespace Affiliate;

use SimpleXMLElement;
use app\lib\helpers\Curl;
use InvalidArgumentException;
use RuntimeException;
use Affiliate\Helpers\XMLHelper;
use Affiliate\Exceptions\MissingFieldException;

class Rakuten
{
    const BASE_API_URL = 'https://api.rakutenmarketing.com';
    const API_NAME     = 'linklocator';
    const API_VERSION  = '1.0';

    const TOKEN_END_POINT = '/token';
    const TOKEN_HEADER = 'a3NQelhIWEd6TERXME85NHNqeDdGcHpYZW1nYTpNTTNkM3YzdlZ4eUk1T0o5c0xIcDRLZlpxN1Fh';

    const HEADER_TYPE_BASIC  = 'Basic';
    const HEADER_TYPE_BEARER = 'Bearer';

    const MERCHANT_BY_ID         = 'getMerchByID';
    const MERCHANT_BY_NAME       = 'getMerchByName';
    const MERCHANT_BY_CATEGORY   = 'getMerchByCategory';
    const MERCHANT_BY_APP_STATUS = 'getMerchByAppStatus';
    const CREATIVE_CATEGORIES    = 'getCreativeCategories';
    const TEXT_LINKS             = 'getTextLinks';
    const BANNER_LINKS           = 'getBannerLinks';
    const DRM_LINKS              = 'getDRMLinks';
    const PRODUCT_LINKS          = 'getProductLinks';

    const VALID_SUB_APIS = [
        self::MERCHANT_BY_ID,
        self::MERCHANT_BY_NAME,
        self::MERCHANT_BY_CATEGORY,
        self::MERCHANT_BY_APP_STATUS,
        self::CREATIVE_CATEGORIES,
        self::TEXT_LINKS,
        self::BANNER_LINKS,
        self::DRM_LINKS,
        self::PRODUCT_LINKS,
    ];

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
     * Request Link
     *
     * @var string
     */
    public $link;
    /**
     * @var string
     */
    public $header = [];
    /**
     * Request Delay
     *
     * @var int
     */
    public $delay = 60;
    /**
     * Request per minute in calling API
     *
     * - Linklocator
     * @var int
     */
    public $requestPerMinute = 5;

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
    public function refreshToken()
    {
        $token = $this->getToken();
    }

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

        return json_decode($accessToken);
    }

    /**
     * Get Request Link
     *
     * @return mixed
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Get Request Header
     *
     * @return string
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Set Request Link To Rakuten
     *
     * @param $endpoint
     */
    public function setLink($endpoint)
    {
        $link = Rakuten::BASE_API_URL.'/'.self::API_NAME.'/'.self::API_VERSION.'/';
        $this->link = $link.$endpoint;
    }

    /**
     * Set Header Request
     * @param $type
     */
    public function setHeader($type, $accessToken = null)
    {
        if (!isset($accessToken)) {
            $token       = $this->getToken();
            $accessToken = $token->access_token;
        }

        $this->header[] = 'Authorization: '.$type. ' '. $accessToken;
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
        $this->setHeader(self::HEADER_TYPE_BASIC, self::TOKEN_HEADER);

        $curl  = new Curl;

        $opt   = [
            'grant_type' => $this->grant,
            'username' => $this->username,
            'password' => $this->password,
            'scope' => $this->scope
        ];

        $token = $curl->post(self::BASE_API_URL.self::TOKEN_END_POINT, $opt, $this->getHeader());

        $this->saveAccessToken($token);
    }

    /**
     * Allows you to download advertiser information by specifying
     * the Application Status ID for the Application Status that
     * you want to get the List of Merchants for.
     *
     * Application status options:
     *   approved
     *   approval extended
     *   wait
     *   temp removed
     *   temp rejected
     *   perm removed
     *   perm rejected
     *   self removed
     *
     * @param string $status
     *
     * @return object
     */
    public function merchantByAppStatus($status)
    {
        $this->setHeader(self::HEADER_TYPE_BEARER);
        $this->setLink(self::MERCHANT_BY_APP_STATUS.'/'.$status);

        $curl     = new Curl;
        $response = $curl->get($this->getLink(),  '', $this->getHeader());

        $xmlData       = new SimpleXMLElement(XMLHelper::tidy($response));
        $checkResponse = json_decode($xmlData);

        if (isset($checkResponse->fault)) {
            sleep(ceil($this->delay / $this->requestPerMinute));
        }

        return $xmlData;
    }

    /**
     * Allows you to download an advertiser’s information by specifying
     * the LinkShare Advertiser ID for that advertiser.
     *
     * @param int $merchantId The LinkShare Advertiser ID
     *
     * @return object
     */
    public function merchantById($merchantId)
    {
        $this->setHeader(self::HEADER_TYPE_BEARER);
        $this->setLink(self::MERCHANT_BY_ID.'/'.$merchantId);

        $curl     = new Curl;
        $response = $curl->get($this->getLink(),  '', $this->getHeader());

        $xmlData  = new SimpleXMLElement(XMLHelper::tidy($response));
        $checkResponse = json_decode($xmlData);

        if (isset($checkResponse->fault)) {
            sleep(ceil($this->delay / $this->requestPerMinute));
        }

        return $xmlData;
    }

    /**
     * Allows you to download an advertiser’s information by specifying the name of the advertiser.
     *
     * @param string $name The name of the advertiser. It must be an exact match.
     *
     * @return $name
     */
    public function merchantByName($name)
    {
        $this->setHeader(self::HEADER_TYPE_BEARER);
        $this->setLink(self::MERCHANT_BY_NAME.'/'.$name);

        $curl     = new Curl;
        $response = $curl->get($this->getLink(),  '', $this->getHeader());

        $xmlData  = new SimpleXMLElement(XMLHelper::tidy($response));

        //@todo Implementation here
        return $name;
    }

    /**
     * Allows you to download advertiser information by specifying the advertiser category.
     *
     * These are the same categories that you see when looking for advertisers in the
     * Programs section of the Publisher Dashboard.
     *
     * @param int $categoryId The category of the advertiser
     *
     * @return $categoryId
     */
    public function merchantByCategory($categoryId)
    {
        $this->setHeader(self::HEADER_TYPE_BEARER);
        $this->setLink(self::MERCHANT_BY_CATEGORY.'/'.$categoryId);

        $curl     = new Curl;
        $response = $curl->get($this->getLink(),  '', $this->getHeader());

        $xmlData  = new SimpleXMLElement(XMLHelper::tidy($response));

        //@todo Implementation here
        return $categoryId;
    }

    /**
     * Provides you the available banner links.
     *
     * To obtain specific banner links, you can filter this request using
     * these parameters: MID, Category, Size, Start Date, and End Date.
     *
     * @param int         $merchantId This is the Rakuten LinkShare Advertiser ID.
     *                                Optional, use -1 as the default value.
     * @param int         $categoryId This is the Creative Category ID.
     *                                It is assigned by the advertiser. Use the Creative Category
     *                                feed to obtain it (not the Advertiser Category Table listed
     *                                in the Publisher Help Center).
     *                                Optional, use -1 as the default value.
     * @param Carbon|null $startDate  This is the start date for the creative, formatted MMDDYYYY.
     *                                Optional, use null as the default value.
     * @param Carbon|null $endDate    This is the end date for the creative, formatted MMDDYYYY.
     *                                Optional, use null as the default value.
     * @param int         $size       This is the banner size code.
     *                                Optional, use -1 as the default value.
     * @param int         $campaignId Rakuten LinkShare retired this feature in August 2011.
     *                                Please enter -1 as the default value.
     * @param int         $page       This is the page number of the results.
     *                                On queries with a large number of results, the system
     *                                returns 10,000 results per page. This parameter helps
     *                                you organize them.
     *                                Optional, use 1 as a default value.
     *
     * @return $data[]
     */
    public function bannerLinks(
        $merchantId = -1,
        $categoryId = -1,
        Carbon $startDate = null,
        Carbon $endDate = null,
        $size = -1,
        $campaignId = -1,
        $page = 1
    ) {
        $data = [];

        return $data;
    }
}