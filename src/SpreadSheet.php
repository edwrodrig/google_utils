<?php
declare(strict_types=1);

namespace edwrodrig\google_utils;

use Google_Service_Sheets_Spreadsheet;

/**
 * Class SpreadSheet
 * This class contains a
 * @package edwrodrig\google_utils
 */
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

    /**
     * Get the Title of the spreadsheet
     * @return string
     */
    public function getTitle() : string {
        return $this->spreadsheet->getProperties()->getTitle();
    }

    /**
     * Get the sheets of this spreadsheet
     *
     * This method yields every sheet in the spreadsheet
     * @return \Generator|Sheet[]
     */
    public function getSheets() {
        foreach ( $this->spreadsheet->getSheets() as $sheet )
            yield new Sheet($this->service, $this->spreadsheet, $sheet);

    }

    /**
     * Get a sheet from this title
     *
     * @param string $title
     * @return Sheet
     * @throws exception\SheetDoesNotExistsException
     */
    public function getSheet(string $title) : Sheet {
        /** @var $sheet Sheet */
        foreach ( $this->getSheets() as $sheet ) {
            if ( $sheet->getTitle() == $title )
                return $sheet;
        }

        throw new exception\SheetDoesNotExistsException($title);
    }

    /**
     * Download the spreadsheet
     *
     * @param string $target_dir
     * @return string
     * @throws exception\WrongSheetFormatException
     */
    public function download(string $target_dir) {

        $target_dir = $target_dir . DIRECTORY_SEPARATOR . $this->getTitle();
        /** @var $sheet Sheet */
        foreach ( $this->getSheets() as $sheet ) {
            $sheet->download($target_dir);
        }

        return $target_dir;
    }

}