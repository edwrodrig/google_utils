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
use Google_Service_Sheets_Spreadsheet;

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

    /**
     * Sheet constructor.
     *
     * A constructor class
     * It is intented to be created by {@see Service service} class or other accessor classed
     * @param Service $service
     * @param Google_Service_Sheets_Spreadsheet $spreadsheet
     * @param Google_Service_Sheets_Sheet $sheet
     */
    public function __construct(Service $service, Google_Service_Sheets_Spreadsheet $spreadsheet, Google_Service_Sheets_Sheet $sheet) {
        $this->service = $service;
        $this->spreadsheet = $spreadsheet;
        $this->sheet = $sheet;
    }

    /**
     * @param string $range in A1 notation, use {@see Sheet::range()} to build the range
     * @return array
     */
    public function getValues(string $range) : array {
        return $this->service->getSpreadSheetValues($this->spreadsheet->getSpreadsheetId(), $range);
    }

    /**
     * Get formatted data
     *
     * This method tries to extract the information of the sheet to a structured singleton or list. Otherwise returnS null.
     * The structured data is a special format of the sheet defined by me that is suitable for storing structured data in a spreadsheet.
     *
     * @return array|null
     * @throws exception\WrongSheetFormatException
     */
    public function getFormattedData() : ?array {
        $values = $this->getValues($this->range('B', 1, 'C', 5)) ?? [];
        $type = $values[1][0] ?? null;
        $startRowIndex = $values[3][0] ?? null;
        $endColumnIndex = $values[4][0] ?? null;



        if ( $type === 'singleton' ) {
            if ( is_null($startRowIndex))
                throw new exception\WrongSheetFormatException('SINGLETON_EXPECTS_VALID_START_ROW_INDEX');
            /**
             * the validation prevents that $startRowIndex is null
             * @noinspection PhpStrictTypeCheckingInspection
             */
            return $this->getSingleton(intval($startRowIndex));
        } else if ( $type === 'list' ) {
            if ( is_null($startRowIndex) )
                throw new exception\WrongSheetFormatException('LIST_EXPECTS_VALID_START_ROW_INDEX');
            if ( is_null($endColumnIndex) )
                throw new exception\WrongSheetFormatException('LIST_EXPECTS_VALID_END_COLUMN_INDEX');

            /**
             * the validation prevents that $startRowIndex and $endColumnIndex is null
             * @noinspection PhpStrictTypeCheckingInspection
             */
            return $this->getList(intval($startRowIndex), $endColumnIndex);
        } else {
            return null;
        }
    }

    /**
     * Get table as a singleton
     *
     * Retrieve the data of a sheet representing a singleton data
     * @param int $startRowIndex
     * @return array
     */
    protected function getSingleton(int $startRowIndex) : array {
        $rows = $this->service->getSpreadSheetValues(
            $this->spreadsheet->getSpreadsheetId(),
            $this->range('A', $startRowIndex, 'C')
        );

        $array = new ScopedArray();

        foreach ( $rows ?? [] as $row ) {
            $scoped_key = $row[0];
            $value = $row[1];
            $array[$scoped_key] = $value;
        }
        return $array->getData();

    }

    /**
     * Get table as a list
     *
     * Retrieve the data of a sheet representing a structuted list data
     * @param int $startRowIndex
     * @param string $endColumnIndex
     * @return array
     */
    protected function getList(int $startRowIndex, string $endColumnIndex) : array {
        $rows = $this->service->getSpreadSheetValues(
            $this->spreadsheet->getSpreadsheetId(),
            $this->range('A', $startRowIndex, $endColumnIndex)
        );

        //the first row are headers
        $headers = array_shift($rows);

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

    /**
     * Get the title of this sheet
     *
     * it is the name in the tab of the sheet
     * @return string
     */
    public function getTitle() : string {
        return $this->sheet->getProperties()->getTitle();
    }

    /**
     * Get a range of this sheet is A1 notation
     *
     * This includes the sheet in the name
     * @param string $startColumnIndex
     * @param int $startRowIndex
     * @param string $endColumnIndex
     * @param int|null $endRowIndex
     * @return string
     */
    public function range(string $startColumnIndex, int $startRowIndex, string $endColumnIndex, ?int $endRowIndex = null) : string {
        return sprintf("%s!%s",
            $this->getTitle(),
            Util::range(
                $startColumnIndex,
                $startRowIndex,
                $endColumnIndex,
                $endRowIndex
            )
        );
    }

}