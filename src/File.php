<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 27-08-18
 * Time: 16:13
 */

namespace edwrodrig\google_utils;

use DateTime;
use edwrodrig\google_utils\exception\FileDoesNotExistException;
use Exception;
use edwrodrig\google_utils\exception\WrongSheetFormatException;
use Google_Service_Drive_DriveFile;

/**
 * Class File
 *
 * @package edwrodrig\google_utils
 */
class File
{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var \Google_Service_Drive_DriveFile
     */
    private $drive_file;

    /**
     * @var DownloadCache|null
     */
    private $download_cache = null;

    public function __construct(Service $service, Google_Service_Drive_DriveFile $drive_file) {
        $this->service = $service;
        $this->drive_file = $drive_file;
    }

    public function setDownloadCache(?DownloadCache $cache) {
        $this->download_cache = $cache;
    }

    /**
     * Get the name of the file
     * @return string
     */
    public function getName() : string {
        return $this->drive_file->getName();
    }

    /**
     * Get the id of the file
     * @return string
     */
    public function getId() : string {
        return $this->drive_file->getId();
    }

    /**
     * Get the modification time
     * @return DateTime
     * @throws Exception
     */
    public function getLastModificationDate() : DateTime {
        return new DateTime($this->drive_file->getModifiedTime());
    }

    /**
     * Is this file a folder
     * @see https://developers.google.com/drive/api/v3/folder
     * @return bool
     */
    public function isFolder() : bool {
        return $this->drive_file->getMimeType() === 'application/vnd.google-apps.folder';
    }

    /**
     * Is this file a spreadsheet
     * @see https://developers.google.com/drive/api/v3/mime-types
     * @return bool
     */
    public function isSpreadsheet() : bool {
        return $this->drive_file->getMimeType() === 'application/vnd.google-apps.spreadsheet';
    }

    /**
     * Is this file a form
     * @see https://developers.google.com/drive/api/v3/mime-types
     * @return bool
     */
    public function isForm() : bool {
        return $this->drive_file->getMimeType() == 'application/vnd.google-apps.form';
    }

    /**
     * Iterate the files inside folder
     *
     * Use this for iterate child files when this file is a folder
     * @see File::isFolder()
     * @return \Generator|void
     */
    public function iterateFiles() {
        if ( !$this->isFolder() )
            return;

        foreach ( $this->service->getFilesInFolder($this->drive_file->getId()) as $file ) {
            $file->setDownloadCache($this->download_cache);
            yield $file;
        }
    }

    /**
     * Get a spreadsheet object
     *
     * Get a spreadsheet if the file is a valid spreadsheet
     * @return Spreadsheet
     */
    public function toSpreadsheet() : Spreadsheet {
        return $this->service->getSpreadSheetById($this->drive_file->getId());
    }

    /**
     * Get file by name
     *
     * @param string $name
     * @return File
     * @throws FileDoesNotExistException
     */
    public function getFileByName(string $name) : File {
        /** @var $file File */
        foreach ( $this->iterateFiles() as $file ) {
            if ($file->getName() === $name )
                return $file;
        }
        throw new FileDoesNotExistException($name);
    }

    /**
     * Download
     *
     * This download correctly the file not matter is it is a filde or folder
     * @param string $target_dir
     * @param null|string $new_filename if you want to change the target filename
     * @return string
     * @throws WrongSheetFormatException
     * @throws Exception
     */
    public function download(string $target_dir, ?string $new_filename = null) : string
    {
        if ( $this->isFolder() ) {
            return $this->downloadFolder($target_dir, $new_filename);
        } else if ( $this->isSpreadsheet() ) {
            return $this->toSpreadsheet()->download($target_dir);
        } else if ( $this->isForm() ) {
            return '__google_form';
        } else {
            return $this->downloadFile($target_dir, $new_filename);
        }
    }

    /**
     * Download file
     *
     * This method is for download the contents when this file is a file
     * It must be used only by {@see File::download}
     * @param string $target_dir
     * @param null|string $new_filename
     * @return string
     * @throws Exception
     * @internal
     */
    protected function downloadFile(string $target_dir, ?string $new_filename) : string {
        $new_filename = $new_filename ?? $this->drive_file->getName();

        $target_filename = $target_dir . DIRECTORY_SEPARATOR . $new_filename;

        if ( $this->download_cache !== NULL ) {
            $result = $this->download_cache->isFileUpToDate($target_filename, $this);

            $this->download_cache->updateFile($target_filename, $this);

            if ( $result )
                return $target_filename;
        }

        if ( !file_exists($target_dir) )
            mkdir($target_dir, 0777, true);

        $response = $this->service->downloadFile($this->drive_file->getId());

        file_put_contents($target_filename, $response->getBody()->getContents());

        return $target_filename;
    }

    /**
     * Download folder
     *
     * This method is for download the contents when this file {@see File::isFolder() is a folder}
     * It must be used only by {@see File::download}
     * @param string $target_dir
     * @param null|string $new_filename
     * @return string
     * @throws Exception
     * @internal
     */
    protected function downloadFolder(string $target_dir, ?string $new_filename) : string {
        $new_filename = $new_filename ?? $this->drive_file->getName();

        $target_dir = $target_dir . DIRECTORY_SEPARATOR . $new_filename;
        if ( !file_exists($target_dir) )
            mkdir($target_dir, 0777, true);

        /** @var $file File */
        foreach ( $this->iterateFiles() as $file ) {
            $file->download($target_dir);
        }

        return $target_dir;
    }


}