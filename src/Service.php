<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21-08-18
 * Time: 14:37
 */

namespace edwrodrig\google_utils;


use Google_Client;
use Google_Service_Sheets;

class Service
{

    /**
     * @var Google_Client
     */
    private $client;
    /**
     * @var null|Google_Service_Sheets
     */
    private $spreadsheet_service = null;

    public function __construct(Google_Client $client) {
        $this->client = $client;
    }

    public function getSpreadSheetService() : Google_Service_Sheets {
        if ( is_null($this->spreadsheet_service) ) {
            $this->spreadsheet_service = new Google_Service_Sheets($this->client);
        }
        return $this->spreadsheet_service;

    }

    public function getSpreadSheetById(string $spreadsheetId) : SpreadSheet {
        $service = $this->getSpreadSheetService();

        return new SpreadSheet($this, $service->spreadsheets->get($spreadsheetId));
    }

    public function getSpreadSheetValues(string $spreadsheetId, string $range) {

    }
}