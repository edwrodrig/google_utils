<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 29-08-18
 * Time: 17:03
 */

namespace edwrodrig\google_utils\exception;

use Exception;

class FileDoesNotExistException extends Exception
{

    /**
     * FileDoesNotExistException constructor.
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        parent::__construct($filename);
    }
}