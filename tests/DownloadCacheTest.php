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
use edwrodrig\google_utils\DownloadCache;
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

class DownloadCacheTest extends TestCase
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


    public function testDownloadFolderWithCache() {
        $service = new Service(self::$client);
        $folder = $service->getFileById('1Z2suK9ah2srGYAaRb1qXTadgwsmc1nLG');
        $this->assertTrue($folder->isFolder());

        $download_cache_file = $this->root->url() . '/cache.json';
        $downloadCache = new DownloadCache($download_cache_file);
        $folder->setDownloadCache($downloadCache);
        $folder_name = $folder->download($this->root->url());

        $downloadCache->resolveHits();
        $downloadCache->save();

        $this->assertEquals($folder->getName(), basename($folder_name));
        $this->assertFileExists($folder_name);
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'B');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt');

        $this->assertFileExists($download_cache_file);
        $data = json_decode(file_get_contents($download_cache_file), true);

        $this->assertArrayHasKey($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt', $data);

        $downloadCache = new DownloadCache($download_cache_file);
        $folder->setDownloadCache($downloadCache);
        $folder_name = $folder->download($this->root->url());

        $downloadCache->resolveHits();
        $downloadCache->save();

        $this->assertEquals($folder->getName(), basename($folder_name));
        $this->assertFileExists($folder_name);
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'B');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt');

        $this->assertFileExists($download_cache_file);
        $data = json_decode(file_get_contents($download_cache_file), true);

        $this->assertArrayHasKey($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt', $data);
    }

}
