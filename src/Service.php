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
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_FileList;
use Google_Service_Sheets;

/**
 * Class Service
 * @package edwrodrig\google_utils
 *
 * @see https://developers.google.com/resources/api-libraries/documentation/sheets/v4/php/latest/class-Google_Service_Sheets.html Service Sheet API
 *
 */
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

    /**
     * @var null|Google_Service_Drive
     */
    private $drive_service = null;

    public function __construct(Google_Client $client) {
        $this->client = $client;
    }

    public function getSpreadSheetService() : Google_Service_Sheets {
        if ( is_null($this->spreadsheet_service) ) {
            $this->spreadsheet_service = new Google_Service_Sheets($this->client);
        }
        return $this->spreadsheet_service;
    }

    public function getDriveService() : Google_Service_Drive {
        if ( is_null($this->drive_service) ) {
            $this->drive_service = new Google_Service_Drive($this->client);
        }
        return $this->drive_service;
    }

    /**
     * @param string $spreadsheetId
     * @return SpreadSheet
     */
    public function getSpreadSheetById(string $spreadsheetId) : SpreadSheet {
        $service = $this->getSpreadSheetService();

        return new SpreadSheet($this, $service->spreadsheets->get($spreadsheetId));
    }

    /**
     * @param string $spreadsheetId
     * @param string $range
     * @return array
     */
    public function getSpreadSheetValues(string $spreadsheetId, string $range) : array {
        $service = $this->getSpreadSheetService();
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues() ?? [];
        return $values;
    }

    public function getFilesInFolder(string $fileId) : Google_Service_Drive_FileList {
        $service = $this->getDriveService();
        /**
         * @var $folder Google_Service_Drive_FileList
         */
        $folder = $service->files->listFiles([
            'pageSize' => 100,
            'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,mimeType,parents)",
            'q' => "'".$fileId."' in parents"
        ]);

        $folder->
    }

    public function getFile() {
        /**
        * @var $file Google_Service_Drive_DriveFile
        */
        $file->getModifiedTime()
    }
}