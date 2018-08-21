<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21-08-18
 * Time: 14:35
 */

namespace edwrodrig\google_utils;

use Google_Service_Sheets_Sheet;

class Sheet
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var SpreadSheet
     */
    private $spreadsheet;

    /**
     * @var \Google_Service_Sheets_Sheet
     */
    private $sheet;

    public function __construct(Service $service, Google_Service_Sheets_Spreadsheet $spreadsheet, Google_Service_Sheets_Sheet $sheet) {
        $this->service = $service;
        $this->spreadsheet = $spreadsheet;
        $this->sheet = $sheet;
    }

    public function getType
}