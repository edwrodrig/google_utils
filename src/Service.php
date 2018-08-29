<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 21-08-18
 * Time: 14:37
 */

namespace edwrodrig\google_utils;


use Generator;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Service_Drive_FileList;
use Google_Service_Sheets;
use GuzzleHttp\Psr7\Response;

/**
 * Class Service
 * @package edwrodrig\google_utils
 *
 * @see https://developers.google.com/resources/api-libraries/documentation/sheets/v4/php/latest/class-Google_Service_Sheets.html Service Sheet API
 * @see https://developers.google.com/resources/api-libraries/documentation/drive/v2/php/latest/index.html Drive API
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

    /**
     * Get Spreadsheet service
     * @return Google_Service_Sheets
     */
    public function getSpreadSheetService() : Google_Service_Sheets {
        if ( is_null($this->spreadsheet_service) ) {
            $this->spreadsheet_service = new Google_Service_Sheets($this->client);
        }
        return $this->spreadsheet_service;
    }

    /**
     * Get Drive Service
     *
     * @internal
     * @return Google_Service_Drive
     */
    public function getDriveService() : Google_Service_Drive {
        if ( is_null($this->drive_service) ) {
            $this->drive_service = new Google_Service_Drive($this->client);
        }
        return $this->drive_service;
    }

    /**
     * Get spreadsheet by Id
     *
     * @internal
     * @param string $spreadsheetId
     * @return SpreadSheet
     */
    public function getSpreadSheetById(string $spreadsheetId) : SpreadSheet {
        $service = $this->getSpreadSheetService();

        return new SpreadSheet($this, $service->spreadsheets->get($spreadsheetId));
    }

    /**
     * Get Spreadsheet values
     *
     *
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

    /**
     * Get files in folder
     *
     * This is used for iteration
     * @param string $fileId
     * @param int $page_size
     * @return Generator|File[]
     */
    public function getFilesInFolder(string $fileId, int $page_size = 100) {
        $service = $this->getDriveService();

        /** @var $folder Google_Service_Drive_FileList */
        $folder = $service->files->listFiles([
            'pageSize' => $page_size,
            'fields' => "nextPageToken, files(id,name,modifiedTime,mimeType,parents)",
            'q' => "'".$fileId."' in parents"
        ]);

        /** @var $file Google_Service_Drive_DriveFile */
        foreach ( $folder as $file )
            yield new File($this, $file);

    }


    /**
     * Download a file from file id
     *
     * Prefer using {@see File::download} instead
     * @param string $fileId
     * @see https://developers.google.com/drive/api/v3/manage-downloads
     * @return Response
     */
    public function downloadFile(string $fileId) : Response {
        $service = $this->getDriveService();

        /** @var $response Response */
        $response = $service->files->get($fileId, ['alt' => 'media']);

        return $response;
    }

    /**
     * Download a file from file id
     *
     * Prefer using {@see File::download} instead
     * Mime types:
     * * text/html
     * * application/zip
     * * text/plain
     * * application/rtf
     * * application/pdf
     * @param string $fileId
     * @param string $mime_type
     * @see https://developers.google.com/drive/api/v3/manage-downloads
     * @return Response
     */
    public function exportFile(string $fileId, string $mime_type) : Response {
        $service = $this->getDriveService();

        /** @var $response Response */
        $response = $service->files->export($fileId, $mime_type, ['alt' => 'media']);

        return $response;
    }

    /**
     * Get a file from fileId
     *
     * @param string $fileId
     * @return File
     */
    public function getFile(string $fileId) : File {
        $service = $this->getDriveService();

        return new File($this, $service->files->get($fileId, ['fields' => 'id,name,modifiedTime,mimeType,parents']));
    }
}