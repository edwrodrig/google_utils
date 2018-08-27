<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: edwin
 * Date: 27-08-18
 * Time: 16:13
 */

namespace edwrodrig\google_utils;


use Google_Service_Drive_DriveFile;

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

    public function getName() : string {
        return $this->drive_file->getName();
    }

    public function getLastModificationDate() {
        return $this->drive_file->getModifiedTime();
    }

    public function download(string $target_dir, ?string $new_filename = null) : Google_Service_Drive_DriveFile {
        /** @var $response
        $response = $this->service->downloadFile($this->drive_file->getId());
        $response->get
    }
}