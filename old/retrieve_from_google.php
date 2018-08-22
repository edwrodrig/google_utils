<?php
require __DIR__ . '/../vendor/autoload.php';

/**
 * Returns an authorized API client.
 * To get credentials:
 * * go to https://console.developers.google.com
 * * select the correct project
 * * go to credentials
 * * click an OAuth credential
 * * click on download json
 * @return Google_Client the authorized client object
 */
function getClient() : Google_Client
{
    putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../config/credentials.json');

    $client = new Google_Client();
    $client->useApplicationDefaultCredentials();
    $client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY, Google_Service_Drive::DRIVE_METADATA_READONLY, Google_Service_Drive::DRIVE_READONLY]);
    return $client;
}

function downloadFile(Google_Service_Drive $service, string $target_dir, Google_Service_Drive_DriveFile $file) {
    $name = $file->getName();
    echo 'Downloading: ' . $name . "\n";
    $response = $service->files->get($file->getId(), ['alt' => 'media']);
    $target_file = $target_dir . DIRECTORY_SEPARATOR . $name;
    @mkdir(dirname($target_file),  0777, true);
    file_put_contents($target_file, $response->getBody()->getContents());
}

function downloadFolder(Google_Service_Drive $service, string $target_folder, Google_Service_Drive_DriveFile $folder) {

    /** @var $files Google_Service_Drive_FileList */
    $files = $service->files->listFiles([
        'pageSize' => 1000,
        'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
        'q' => "'".$folder->getId() ."' in parents"
    ]);

    /** @var $file Google_Service_Drive_DriveFile */
    foreach ( $files as $file )
        downloadFile($service, $target_folder, $file);
}

function downloadSheet(Google_Service_Sheets $service, Google_Service_Sheets_Spreadsheet $spreadsheet, Google_Service_Sheets_Sheet $sheet, string $target_dir) {
    $title = $sheet->getProperties()->getTitle();
    $range = $title . '!B1:4';

    $spreadsheetId = $spreadsheet->getSpreadsheetId();
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();
    $type = $values[1][0] ?? null;
    $start = $values[3][0] ?? null;

    if ( $type === 'singleton') {
        getSingleton($service, $spreadsheetId, $title, $start);
    } else if ( $type === 'list' ) {
        getList($service, $spreadsheetId, $title, $start);
    }



}

// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);
$folderId = '1Qv8Y_jiT1BfTZin0o_O6yeuBIjBaNcIu';
/**
 * @var $folder Google_Service_Drive_FileList
 */
$folder = $service->files->listFiles([
    'pageSize' => 100,
    'fields' => "nextPageToken, files(contentHints/thumbnail,fileExtension,iconLink,id,name,size,thumbnailLink,webContentLink,webViewLink,mimeType,parents)",
    'q' => "'".$folderId."' in parents"
]);

var_dump($folder);

/**
 * @var $file Google_Service_Drive_DriveFile
 */
foreach ( $folder->files as $file) {
    $name = $file->getName();
    echo $name, "\n";
    if ( $name == 'data' ) {
        $service = new Google_Service_Sheets($client);

        $spreadsheetId = $file->getId();
        $spreadsheet = $service->spreadsheets->get($spreadsheetId);
        /** @var $sheet Google_Service_Sheets_Sheet */
        foreach ( $spreadsheet->getSheets() as $sheet ) {
            $title = $sheet->getProperties()->getTitle();
            $range = $title . '!B1:4';

            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();
            $type = $values[1][0] ?? null;
            $start = $values[3][0] ?? null;

            if ( $type === 'singleton' ) {
                $data = getSingleton($service, $spreadsheetId, $title, $start);
                file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . '/../data' . DIRECTORY_SEPARATOR . $title . '.json', json_encode($data, JSON_PRETTY_PRINT));
            } else if ( $type === 'list') {
                $data = getList($service, $spreadsheetId, $title, $start);
                file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . '/../data' . DIRECTORY_SEPARATOR . $title . '.json', json_encode($data, JSON_PRETTY_PRINT));
            }
        }

    } else if ( $name == 'images' ) {
        downloadFolder($service, __DIR__ . '/../data/images', $file);

    } else if ( $name == 'files' ) {
        downloadFolder($service, __DIR__ . '/../data/files', $file);

    }
}


// Prints the names and majors of students in a sample spreadsheet:
// https://docs.google.com/spreadsheets/d/1BxiMVs0XRA5nFMdKvBdBZjgmUUqptlbs74OgvE2upms/edit


function array_setter(&$data, $scope, $value)
{
    $levels = explode('.', $scope);
    $temp = &$data;

    foreach ($levels as $key) {
        $temp = &$temp[$key];
    }
    $temp = $value;
}

function getSingleton(Google_Service_Sheets $service, $spreadsheetId, $sheet, $start) {
    $range = $sheet . '!' . $start . ':B';
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();

    if (empty($values)) {
        print "No data found.\n";
        return [];
    } else {
        $data = [];
        foreach ($values as $row) {
            array_setter($data, $row[0], $row[1]);

        }
        var_dump($data);
        return $data;
    }

}

function getList(Google_Service_Sheets $service, $spreadsheetId, $sheet, $start) : array {
    $range = $sheet . '!' . $start . ':AA';
    $response = $service->spreadsheets_values->get($spreadsheetId, $range);
    $values = $response->getValues();

    if (empty($values)) {
        print "No data found.\n";
        return [];
    } else {
        $data = [];

        $begin = true;
        $keys = [];
        $data = [];
        foreach ($values as $row) {
            if ( $begin ) {
                $begin = false;
                $keys = $row;
                continue;
            }

            $element = [];

            foreach ( $row as $index => $column ) {
                if ( isset($keys[$index]) ) {
                    array_setter($element, $keys[$index], $column);
                }
            }
            $data[] = $element;


        }
        return $data;
    }

}
