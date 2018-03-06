<?php
/**
 * Created by PhpStorm.
 * User: angelogabisan
 * Date: 05/03/2018
 * Time: 1:34 PM
 */

namespace Affiliate;

use app\lib\helpers\Curl;

class Commission
{
    const BASE_API_URL = 'https://api.commissionfactory.com.au';
    const API_NAME     = '';
    const API_VERSION  = 'V1';

    /**
     * Content Type of a requested api call
     *
     * JSON, XML etc...
     * @var string
     */
    public $contentType = 'application/JSON';

    /**
     * Merchant Status
     *
     * @var string
     */
    public $status;

    /**
     * API KEY
     *
     * @var string
     */
    protected $apiKey;

    public function __construct($token)
    {
        $this->apiKey = $token;
    }


}