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

    public function __construct(Service $service, Google_Service_Drive_DriveFile $drive_file) {
        $this->service = $service;
        $this->drive_file = $drive_file;
    }

    /**
     * Get the name of the file
     * @return string
     */
    public function getName() : string {
        return $this->drive_file->getName();
    }

    /**
     * Get the modification time
     * @return DateTime
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
     * Iterate the files inside folder
     *
     * Use this for iterate child files when this file is a folder
     * @see File::isFolder()
     * @return \Generator|void
     */
    public function iterateFiles() {
        if ( !$this->isFolder() )
            return;

        foreach ( $this->service->getFilesInFolder($this->drive_file->getId()) as $files ) {
            yield $files;
        }

    }

    /**
     * Download
     *
     * This download correctly the file not matter is it is a filde or folder
     * @param string $target_dir
     * @param null|string $new_filename if you want to change the target filename
     * @return string
     */
    public function download(string $target_dir, ?string $new_filename = null) : string
    {
        if ( $this->isFolder() ) {
            return $this->downloadFolder($target_dir, $new_filename);

        } else {
            return $this->downloadFile($target_dir, $new_filename);
        }
    }

    /**
     * Download file
     *
     * This method is for download the contents when this file is a file
     * It must be used only by {@see File::download}
     * @internal
     * @param string $target_dir
     * @param null|string $new_filename
     * @return string
     */
    protected function downloadFile(string $target_dir, ?string $new_filename) : string {
        $response = $this->service->downloadFile($this->drive_file->getId());

        $new_filename = $new_filename ?? $this->drive_file->getName();
        if ( !file_exists($target_dir) )
            mkdir($target_dir, 0777, true);

        $target_filename = $target_dir . DIRECTORY_SEPARATOR . $new_filename;
        file_put_contents($target_filename, $response->getBody()->getContents());

        return $target_filename;
    }

    /**
     * Download folder
     *
     * This method is for download the contents when this file {@see File::isFolder() is a folder}
     * It must be used only by {@see File::download}
     * @internal
     * @param string $target_dir
     * @param null|string $new_filename
     * @return string
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