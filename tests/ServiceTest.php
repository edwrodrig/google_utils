<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 25-08-18
 * Time: 20:26
 */

namespace test\edwrodrig\google_utils;

use DateTime;
use edwrodrig\google_utils\exception\FileDoesNotExistException;
use edwrodrig\google_utils\exception\SheetDoesNotExistsException;
use edwrodrig\google_utils\File;
use edwrodrig\google_utils\Service;
use edwrodrig\google_utils\Sheet;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Sheets;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;
    public function setUp() {
        $this->root = vfsStream::setup();
    }

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
     * @throws \edwrodrig\google_utils\exception\SheetDoesNotExistsException
     * @throws \edwrodrig\google_utils\exception\WrongSheetFormatException
     */
    public function testRetrieveSpreadSheetFromFile() {
        $service = new Service(self::$client);
        $file = $service->getFile('1XmYTa7fS-W1QpiEgp0OnMZCrOyJ5InCVA-nl7Y0kT_A');
        $this->assertTrue($file->isSpreadsheet());

        $spreadsheet = $file->toSpreadsheet();

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

    public function testDownloadFile() {
        $service = new Service(self::$client);
        $response = $service->downloadFile('1fPKlWCD7UrB6vfdHM7xJ4JCGlMUvqzaO');

        $this->assertEquals(14, $response->getBody()->getSize());
        $this->assertEquals('SOME TEST FILE', $response->getBody()->getContents());
    }

    public function testGetFile() {
        $service = new Service(self::$client);
        $file = $service->getFile('1fPKlWCD7UrB6vfdHM7xJ4JCGlMUvqzaO');

        $this->assertEquals('test.txt', $file->getName());

        $this->assertGreaterThan(new DateTime('2018-08-27'), $file->getLastModificationDate());

        $filename = $file->download($this->root->url());
        $this->assertEquals('test.txt', basename($filename));
        $this->assertFileExists($filename);
        $this->assertEquals(14, filesize($filename));


        $filename = $file->download($this->root->url(), 'other.txt');
        $this->assertEquals('other.txt', basename($filename));
        $this->assertFileExists($filename);
        $this->assertEquals(14, filesize($filename));

    }

    public function testGetFilesInFolder() {
        $service = new Service(self::$client);
        $names = array_map(
            function(File $file) { return $file->getName(); },
            iterator_to_array($service->getFilesInFolder('1RpFNqAwvm2hPflHu52mSgh9S1wclSEmC'))
        );

        $this->assertEquals(['kula.png', 'test.txt'], $names);
    }

    /**
     * @throws FileDoesNotExistException
     */
    public function testGetFileInFolder() {
        $service = new Service(self::$client);

        $folder = $service->getFile('1RpFNqAwvm2hPflHu52mSgh9S1wclSEmC');
        $this->assertTrue($folder->isFolder());

        $file = $folder->getFileByName('kula.png');
        $this->assertEquals('kula.png', $file->getName());
    }

    /**
     * @expectedException \edwrodrig\google_utils\exception\FileDoesNotExistException
     * @expectedExceptionMessage not_existant.png
     * @throws \edwrodrig\google_utils\exception\FileDoesNotExistException
     */
    public function testGetNotExistantFileInFolder() {
        $service = new Service(self::$client);

        $folder = $service->getFile('1RpFNqAwvm2hPflHu52mSgh9S1wclSEmC');
        $this->assertTrue($folder->isFolder());

        $folder->getFileByName('not_existant.png');
    }

    public function testDownloadFolder() {
        $service = new Service(self::$client);
        $folder = $service->getFile('1Z2suK9ah2srGYAaRb1qXTadgwsmc1nLG');
        $this->assertTrue($folder->isFolder());
        $folder_name = $folder->download($this->root->url());
        $this->assertEquals($folder->getName(), basename($folder_name));
        $this->assertFileExists($folder_name);
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'B');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt');
    }

    public function testExportFile() {
        $service = new Service(self::$client);
        $file = $service->exportFile('1BFd8L1_pIWMI_8WHR31jZ1wXw-fcY_LRY_KwYwx3twM', 'text/html');
        $contents = $file->getBody()->getContents();
        $this->assertStringStartsWith('<html><head><meta content="text/html;', $contents);

        $file = $service->exportFile('1BFd8L1_pIWMI_8WHR31jZ1wXw-fcY_LRY_KwYwx3twM', 'text/plain');
        $contents = $file->getBody()->getContents();
        $this->assertContains("Hola", $contents);
        $this->assertContains("como te va", $contents);

        $file = $service->exportFile('1BFd8L1_pIWMI_8WHR31jZ1wXw-fcY_LRY_KwYwx3twM', 'application/rtf');
        $contents = $file->getBody()->getContents();
        $this->assertContains("Hola", $contents);


    }
}
