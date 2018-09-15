<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 22-08-18
 * Time: 16:41
 */

namespace edwrodrig\google_utils;

use ArrayAccess;

/**
 * Class ScopedArray
 *
 * A class to create associative arrays with string that represent scoped keys.
 * Scoped keys are those use a dot between level example: level1.level2.level3.
 * You must use this class in the following way
 *
 * ```
 * $array = new ScopedArray();
 * $array['name'] = 'Edwin';
 * $array['address.street'] = 'Street 2';
 * $array['address.number'] = 'number';
 * $array->getData();
 * ```
 * @package edwrodrig\google_utils
 */
class ScopedArray implements ArrayAccess
{

    /**
     * The inner array that store the data
     * @var array
     */
    private $data = [];

    /**
     * Convert a string to a scope
     * @param string $scope
     * @return array
     */
    protected static function getScope(string $scope) : array {
        $levels = explode('.', $scope);
        return $levels;

    }

    public function offsetSet($offset, $value) {
        if ( is_null($offset) )
            return;

        if ( is_string($offset) && empty(trim($offset)) )
            return;

        $currentScope = &$this->data;
        foreach ($this->getScope($offset) as $key) {
            $currentScope = &$currentScope[$key];
        }
        $currentScope = $value;
    }

    /**
     * isset is not implemented
     * @param mixed $offset
     * @return bool|void
     */
    public function offsetExists($offset) {;}

    /**
     * getter is not implemented
     * @param mixed $offset
     * @return bool|void
     */
    public function offsetGet($offset) {;}

    /**
     * unset is not implemented
     * @param mixed $offset
     * @return bool|void
     */
    public function offsetUnset($offset) {;}

    /**
     * Use this to retrieve the resulting array after the insertion
     * @return array
     */
    public function getData() : array {
        return $this->data;
    }

    /**
     * Create an associative array from pairs of key values
     * @param array $rows
     * @return array
     */
    public static function arrayFromPairs(array $rows) : array {
        $array = new ScopedArray();

        foreach ( $rows ?? [] as $row ) {
            $scoped_key = $row[0] ?? null;
            $value = $row[1] ?? null;
            $array[$scoped_key] = $value;
        }
        return $array->getData();
    }

    /**
     * Create a list of associative arrays from rows
     *
     * @param array $headers
     * @param array $rows
     * @return array
     */
    public static function arrayFromRows(array $headers, array $rows) : array {
        //the array with the results
        $list = [];

        foreach ( $rows as $row ) {

            $element = new ScopedArray();
            foreach ( $row as $index => $column ) {

                $scoped_key = $headers[$index];
                $value = $column;
                $element[$scoped_key] = $value;
            }

            $list[] = $element->getData();
        }
        return $list;
    }
}