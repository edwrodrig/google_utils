<?php

use edwrodrig\google_utils\Sheet;

require_once __DIR__ . '/../vendor/autoload.php';

putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/credentials.json');

$client = new Google_Client();
$client->useApplicationDefaultCredentials();
$client->setScopes([Google_Service_Sheets::SPREADSHEETS_READONLY, Google_Service_Drive::DRIVE_METADATA_READONLY, Google_Service_Drive::DRIVE_READONLY]);

/*
 * https://docs.google.com/spreadsheets/d/1RfXf4z9cFau_ZNB5Ffr12e8aOwdvgrkMjn8_YZPYELA/edit?usp=sharing
 */
$service = new \edwrodrig\google_utils\Service($client);
$spreadsheet = $service->getSpreadSheetById('1nicx5Zh75o7e3Bk3tX86-vbnYalZRJBxZav_foi2uFg');

/** @var Sheet $sheet */
foreach ( $spreadsheet->getSheets() as $sheet ) {
    var_dump($sheet->getFormattedData());
}

