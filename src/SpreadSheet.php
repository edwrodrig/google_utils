<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21-08-18
 * Time: 14:35
 */

namespace edwrodrig\google_utils;


use Google_Service_Sheets_Spreadsheet;

class SpreadSheet
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var \Google_Service_Sheets_Spreadsheet
     */
    private $spreadsheet;

    public function __construct(Service $service, Google_Service_Sheets_Spreadsheet $spreadsheet) {
        $this->service = $service;
        $this->spreadsheet = $spreadsheet;
    }

    public function getSheets() {
        foreach ( $this->spreadsheet->getSheets() as $sheet )
            yield new Sheet($this->service, $this->spreadsheet, $sheet);

    }
}