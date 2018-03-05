<?php

namespace Affiliate;

use Affiliate\Rakuten;
use Affiliate\Commission;

class Affiliate
{
    const AFFILIATE_RAKUTEN = 'rakuten';
    const AFFILIATE_COMMISSION_FACTORY = 'commission';

    /**
     * Specify the type of affiliate
     * will be initializing.
     *
     * @var $type
     */
    public $type;

    /**
     * Configuration of The Type Of
     * Affiliate that is going to be
     * passed on this class.
     *
     * e.g :
     * $options = [
     *  'api_key' => '',
     *  'api_secrete' => ''
     * ];
     * @var array
     */
    public $options = [];

    /**
     * Returns Model base on $type
     *
     * @var $model
     */
    public $model;

    /**
     * Affiliate constructor.
     * @param $type
     * @param array $options
     */
    public function __construct($type, $options = [])
    {
        $this->options = ($options) ? $options : null;

        $this->type    = $type;
    }

    /**
     * @return \Affiliate\Commission
     * @return \Affiliate\Rakuten
     */
    public function getModel()
    {
        $model = null;

        switch ($this->type) :
            case self::AFFILIATE_RAKUTEN;

                $model = new Rakuten($this->options);
                $model->loadAccessToken();

                break;

            case self::AFFILIATE_COMMISSION_FACTORY;

                $model = new Commission($this->options);

                break;
        endswitch;

        return $model;
    }
}