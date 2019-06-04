<?php
declare(strict_types=1);


namespace edwrodrig\google_utils;


use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Sheets;

/**
 * Class EasyDownloader
 *
 * A class to conveniently download a Google Drive folder
 * @package edwrodrig\google_utils
 */
class EasyDownloader
{

    private $credentialsPath;

    private $googleDriveFolderId;

    private $targetFolder;

    /**
     * @var null|string
     */
    private $downloadCacheFile = null;

    /**
     * @param string $credentialsPath
     * @return EasyDownloader
     * @throws Exception
     */
    public function setCredentials(string $credentialsPath) : self {
        if ( !file_exists($credentialsPath) )
            throw new Exception(sprintf("Credentials does not exists [%s]", $credentialsPath));

        $this->credentialsPath = $credentialsPath;

    }

    public function setGoogleDriveFolderId(string $folderId) : self {
        $this->googleDriveFolderId = $folderId;
    }

    public function setTargetFolder(string $targetFolder) : self {
        $this->targetFolder = $targetFolder;
    }

    public function setDownloadCacheFile(string $cacheFile) : self {
        $this->downloadCacheFile = $cacheFile;
    }

    /**
     * @throws Exception
     */
    public function download() {
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $this->credentialsPath);

        $client = new Google_Client();
        $client->useApplicationDefaultCredentials();
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY, Google_Service_Drive::DRIVE_METADATA_READONLY, Google_Service_Drive::DRIVE_READONLY]);

        $service = new Service($client);
        $folder = $service->getFileById($this->googleDriveFolderId);

        $downloadCache = null;
        if ( !is_null($this->downloadCacheFile) )
            $downloadCache = new DownloadCache($this->downloadCacheFile);

        $folder->setDownloadCache($downloadCache);

        $folder->download($this->targetFolder);

        $downloadCache->resolveHits();
        $downloadCache->save();

    }

}