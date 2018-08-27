<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 27-08-18
 * Time: 12:14
 */

namespace edwrodrig\google_utils\exception;


use Exception;

class SheetDoesNotExistsException extends Exception
{

    /**
     * SheetDoesNotExistsException constructor.
     * @param string $title
     */
    public function __construct(string $title)
    {
        parent::__construct($title);
    }
}