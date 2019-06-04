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
use edwrodrig\google_utils\EasyDownloader;
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

class EasyDownloaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;
    public function setUp() {
        $this->root = vfsStream::setup();
    }

    public function testDownloader() {

        $folder_name = $this->root->url() . '/data';
        $download_cache_file = $this->root->url() . '/cache.json';

        $downloader = new EasyDownloader();
        $downloader
            ->setCredentials(__DIR__ . '/files/credentials.json')
            ->setGoogleDriveFolderId('1Z2suK9ah2srGYAaRb1qXTadgwsmc1nLG')
            ->setTargetFolder($folder_name)
            ->setDownloadCacheFile($download_cache_file)
            ->setDownloadCacheBaseDir($this->root->url() . '/')
            ->download();

        $this->assertFileExists($folder_name);
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'B');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C');
        $this->assertFileExists($folder_name . DIRECTORY_SEPARATOR . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt');

        $this->assertFileExists($download_cache_file);
        $data = json_decode(file_get_contents($download_cache_file), true);

        $this->assertArrayHasKey('data/' . 'A' . DIRECTORY_SEPARATOR . 'C' . DIRECTORY_SEPARATOR . 'test.txt', $data);
    }

}
