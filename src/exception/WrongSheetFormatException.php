<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 22-08-18
 * Time: 16:54
 */

namespace edwrodrig\google_utils\exception;

use Exception;

class WrongSheetFormatException extends Exception
{

    /**
     * WrongSheetFormatException constructor.
     * @param string $string
     */
    public function __construct(string $string)
    {
        parent::__construct($string);
    }
}