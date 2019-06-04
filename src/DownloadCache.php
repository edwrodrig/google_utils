<?php
declare(strict_types=1);


namespace edwrodrig\google_utils;


use DateTime;

class DownloadCache
{

    /**
     * @var array This is a key value map of Google File Id and Last modification time
     */
    private $map = [];

    /**
     * @var array cache hits
     */
    private $cache_hits = [];

    /**
     * @var string filename
     */
    private $cache_filename;

    private $base_dir;

    public function __construct(string $cache_filename, string $base_dir) {
        $this->cache_filename = $cache_filename;
        $this->base_dir = $base_dir;
        $this->prepareTargetDir();

        if ( !file_exists($this->cache_filename) )
            return;

        $file_data = file_get_contents($this->cache_filename, true);
        $json_data = json_decode($file_data, true);
        if ( $json_data === FALSE )
            return;

        $this->map = $json_data;
    }

    /**
     * @param string $target_path
     * @param File $file
     * @return bool
     * @throws \Exception
     */
    public function isFileUpToDate(string $target_path, File $file) : bool {
        $target_path = str_replace( $this->base_dir, '', $target_path);

        $currentFileId = $file->getId();
        $currentLastModificationDate = self::formatDateTime($file->getLastModificationDate());

        if ( !isset($this->map[$target_path]) ) return false;

        $lastModificationDate = $this->map[$target_path]['last_modification_date'];
        $fileId = $this->map[$target_path]['file_id'];

        if ( $fileId != $currentFileId ) return false;

        if ( !file_exists($target_path) ) return false;

        return $currentLastModificationDate <= $lastModificationDate;
    }

    private static function formatDateTime(DateTime $dateTime) : string {
        return $dateTime->format('Y-m-dTY-m-d H:i:s');
}

    /**
     * @param string $target_path
     * @param File $file
     * @throws \Exception
     */
    public function updateFile(string $target_path, File $file) : void {

        $target_path = str_replace( $this->base_dir, '', $target_path);
        $fileId = $file->getId();
        $lastModificationTime = self::formatDateTime($file->getLastModificationDate());
        $this->map[$target_path] = [
            'last_modification_date' => $lastModificationTime,
            'file_id' => $fileId
        ];

        $this->cache_hits[$target_path] = true;
    }

    /**
     * This function deletes files that doesn't exist in the downloaded folder
     */
    public function resolveHits() {

        $deleted = [];
        foreach ( $this->map as $target_path => $data ) {
            if ( !isset($this->cache_hits[$target_path] ) )
                $deleted[] = $target_path;
        }

        foreach ( $deleted as $deleted_file ) {
            if ( file_exists($deleted_file) )
                unlink($this->base_dir . $deleted_file);
            unset($this->map[$deleted_file]);
        }
    }

    private function prepareTargetDir() {
        $target_dir = dirname($this->cache_filename);
        if ( !file_exists($target_dir) )
            mkdir($target_dir, 0777, true);
    }

    public function save() {
        $this->prepareTargetDir();

        file_put_contents($this->cache_filename, json_encode($this->map, JSON_PRETTY_PRINT));
    }
}