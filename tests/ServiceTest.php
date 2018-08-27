<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 25-08-18
 * Time: 20:26
 */

namespace test\edwrodrig\google_utils;

use edwrodrig\google_utils\exception\SheetDoesNotExistsException;
use edwrodrig\google_utils\exception\WrongSheetFormatException;
use edwrodrig\google_utils\Service;
use edwrodrig\google_utils\Sheet;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Sheets;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    private static $client;

    public static function setUpBeforeClass() {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/files/credentials.json');

        self::$client = new Google_Client();
        self::$client->useApplicationDefaultCredentials();
        self::$client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY, Google_Service_Drive::DRIVE_METADATA_READONLY, Google_Service_Drive::DRIVE_READONLY]);

    }


    /**
     * @throws \edwrodrig\google_utils\exception\SheetDoesNotExistsException
     * @throws \edwrodrig\google_utils\exception\WrongSheetFormatException
     */
    public function testRetrieveSpreadSheetSingleton() {
        $service = new Service(self::$client);
        $spreadsheet = $service->getSpreadSheetById('1XmYTa7fS-W1QpiEgp0OnMZCrOyJ5InCVA-nl7Y0kT_A');

        $sheet = $spreadsheet->getSheet('singleton');
        $this->assertInstanceOf(Sheet::class, $sheet);

        $this->assertEquals('singleton', $sheet->getTitle());
        $this->assertEquals(
            ['name' => 'Edwin', 'surname' => 'Rodríguez', 'mail' => ['user' => 'edwin', 'domain' => 'mail.cl']],
            $sheet->getFormattedData()
        );

    }

    /**
     * @expectedException \edwrodrig\google_utils\exception\SheetDoesNotExistsException
     * @expectedExceptionMessage not_existant
     */
    public function testRetrieveSpreadSheetNotExistant() {
        $service = new Service(self::$client);
        $spreadsheet = $service->getSpreadSheetById('1XmYTa7fS-W1QpiEgp0OnMZCrOyJ5InCVA-nl7Y0kT_A');

        $spreadsheet->getSheet('not_existant');

    }

    /**
     * @throws SheetDoesNotExistsException
     * @throws \edwrodrig\google_utils\exception\WrongSheetFormatException
     */
    public function testRetrieveSpreadSheetList() {
        $service = new Service(self::$client);
        $spreadsheet = $service->getSpreadSheetById('1XmYTa7fS-W1QpiEgp0OnMZCrOyJ5InCVA-nl7Y0kT_A');

        $sheet = $spreadsheet->getSheet('list');

        $this->assertEquals([
            ['name' => 'Edwin', 'surname' => 'Rodríguez', 'mail' => ['user' => 'edwin', 'domain' => 'mail.cl']],
            ['name' => 'Amanda', 'surname' => 'Morales', 'mail' => ['user' => 'amanda', 'domain' => 'mole.com']]
        ], $sheet->getFormattedData());
    }

    /**
     * @throws SheetDoesNotExistsException
     * @throws \edwrodrig\google_utils\exception\WrongSheetFormatException
     */
    public function testRetrieveInvalidFormat() {
        $service = new Service(self::$client);
        $spreadsheet = $service->getSpreadSheetById('1XmYTa7fS-W1QpiEgp0OnMZCrOyJ5InCVA-nl7Y0kT_A');

        $sheet = $spreadsheet->getSheet('invalid_type');

        $this->assertEquals([
            ['name', 'test 1'],
            ['type', 'invalid_type'],
            ['desc', 'some description'],
            ['start', '7'],
            ['end', 'D']
            ],
            $sheet->getValues($sheet->range('A',1, 'B', 5))
        );
        $this->assertNull($sheet->getFormattedData());

    }
}
