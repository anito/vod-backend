<?php

namespace App\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\Controller\Component;
use Cake\Utility\Security;
use Cake\Event\Event;

class SaltComponent extends Component
{


    /**
     * Default configuration
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * Constructor
     *
     * @param \Cake\Controller\ComponentRegistry $registry A ComponentRegistry for this component
     * @param array $config Array of config.
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);
    }

    function startup(Event $event)
    {

        /** @var \Cake\Controller\Controller $controller */
        $controller = $event->getSubject();
        $request = $controller->getRequest();
    }

    function convert($text, $encode = true)
    {

        $salt = Security::getSalt();

        if (!$encode) {
            $text = base64_decode(strtr($text, '-_,.', '+/=%'));
            // $text = base64_decode(rawurldecode($text));
        }

        // remove the spaces in the key
        $salt = str_replace(' ', '', $salt);
        if (strlen($salt) < 8) {
            $salt = str_pad("a", 10, $salt);
        }
        // set key length to be no more than 32 characters
        $key_len = strlen($salt);
        if ($key_len > 32) {
            $key_len = 32;
        }

        // A wee bit of tidying in case the key was too long
        $salt = substr($salt, 0, $key_len);

        // We use this a couple of times or so
        $text_len = strlen($text);

        // fill key with the bitwise AND of the ith key character and 0x1F, padded to length of text.
        $lomask = str_repeat("\x1f", $text_len); // Probably better than str_pad
        $himask = str_repeat("\xe0", $text_len);
        $k = str_pad("", $text_len, $salt); // this one _does_ need to be str_pad
        // {en|de}cryption algorithm
        $text = (($text ^ $k) & $lomask) | ($text & $himask);

        if ($encode) {
            return strtr(base64_encode($text), '+/=%', '-_,.');
            // return rawurlencode(base64_encode($text));
        } else {
            return $text;
        }
    }
}
