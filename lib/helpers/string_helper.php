<?php
/**
 * This file houses the MpmStringHelper class.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 * @license    http://www.opensource.org/licenses/bsd-license.php  The New BSD License
 * @link       http://code.google.com/p/mysql-php-migrations/
 */

/**
 * The MpmStringHelper class holds a number of static methods which manipulate strings.
 *
 * @package    mysql_php_migrations
 * @subpackage Helpers
 */
class MpmStringHelper
{

    /**
     * Coverts a string from this_notation to ThisNotation (CamelCase)
     *
     * @param string $no_camel the string in this_notation
     *
     * @return string
     */
    static public function strToCamel($no_camel)
    {
        // do not alter string if there are no underscores
        if (stripos($no_camel, '_') == false)
        {
            return $no_camel;
        }
        $no_camel = strtolower($no_camel);
        $no_camel = str_replace('_', ' ', $no_camel);
        $no_camel = ucwords($no_camel);
        $array = explode(' ', $no_camel);
        $camel = '';
        foreach ($array as $key => $part)
        {
            if ($key == 0)
            {
                $camel .= strtolower($part);
            }
            else
            {
                $camel .= $part;
            }
        }
        return $camel;
    }

    /**
     * Converts a string from CamelCaps to this_notation.
     *
     * @param string $camel a string in CamelCaps
     *
     * @return string
     */
    static public function camelToLower($camel)
    {
        // split up the string into an array according to the uppercase characters
        $array = preg_split('/([A-Z][^A-Z]*)/', $camel, (-1), PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $array = array_map('strtolower', $array);
        // create our string
        $lower = '';
        foreach ($array as $part)
        {
            $lower .= $part . '_';
        }
        $lower = substr($lower, 0, strlen($lower) - 1);
        return $lower;
    }

}

?>