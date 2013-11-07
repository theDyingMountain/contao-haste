<?php

namespace Haste;

class Haste extends \Controller
{
    /**
     * Current object instance (Singleton)
     * @var Haste
     */
    protected static $objInstance;

    /**
     * Allow access to all protected Contao Controller methods
     * @param   string Method name
     * @param   mixed Arguments
     */
    public function call($name, $arguments=null)
    {
        $arguments = $arguments === null ? array() : (is_array($arguments) ? $arguments : array($arguments));

        return call_user_func_array(array($this, $name), $arguments);
    }

    /**
     * Instantiate the Haste object
     * @return  object
     */
    public static function getInstance()
    {
        if (null === static::$objInstance) {
            static::initialize();
            static::$objInstance = new static();
        }
    }
}