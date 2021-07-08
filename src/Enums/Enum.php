<?php

declare(strict_types=1);

namespace UnicoETL\Enums;

use ReflectionClass;

/**
 * Enum class
 * Helper class for creating and emulating an enumerate behavior.
 * 
 * Since enums are only available on PHP 8.1, I'm still using classes for this.
 *
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
abstract class Enum
{
    /**
     * Return an array containing the values of all 
     * the defined constants on the class.
     * 
     * @return array
     */
    public static function getConstants()
    {
        $oClass = new ReflectionClass(static::class);
        return array_values(array: $oClass->getConstants());
    }
}
