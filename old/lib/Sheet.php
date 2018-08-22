<?php

namespace ephp\google;

class Sheet {

public $api_key;
public $sheet_id;
public $token;

static function range($r) {
  if ( is_string($r['sheetId']) ) {
    $x1 = $r['startColumnIndex'] ?? null;
    $x2 = $r['endColumnIndex'] ?? null;

    $y1 = $r['startRowIndex'] ?? null;
    $y2 = $r['endRowIndex'] ?? null;

    if ( is_null($x1) || is_null($x2) || $x2 - $x1 < 1 ) $x1 = $x2 = '';
    else {
      $letters = range('A', 'Z');
      $x2 -= 1;
      $x1 = $letters[$x1];
      $x2 = $letters[$x2];
    }
    if ( is_null($y1) || is_null($y2) || $y2 - $y1 < 1 ) $y1 = $y2 = '';
    else {
      $y1 += 1;
    }

    $range = "$x1$y1:$x2$y2";

    if ( $range == ':' ) return $r['sheetId'];
    else return sprintf("%s!%s", $r['sheetId'], $range);
  } else return $r;
}

static function user_entered_value($value, $key = '') {
  if ( is_numeric($value) && $key != 'name' ) return ['numberValue' => $value];
  else return ['stringValue' => $value];
}

function request($type = '', $content = null) {
  $context = [ 'header'  => "Content-type: application/json\r\nAuthorization: Bearer " . $this->token, 'ignore_errors' => true];
  if ( is_null($content) ) {
    $context['method'] = 'GET';
  } else {
    var_dump($content);
    $context['method'] = 'POST';
    $context['content'] = json_encode($content);
  }

  $context = stream_context_create(['http' => $context]);

  $params = http_build_query(['key' => $this->api_key]);
  return json_decode(file_get_contents("https://sheets.googleapis.com/v4/spreadsheets/" . $this->sheet_id . $type . '?' . $params, false, $context), true);
}

function update($range, $values) {
  return $this->request("/values:batchUpdate", [
    "valueInputOption" => "USER_ENTERED",
    "data" => [
        "range" => $range,
        "majorDimension"=> "ROWS",
        "values" => $values
    ]]);
}

function get_sheet_id($sheet_name) {
  $data = $this->get_info();
  var_dump($data);
  foreach ( $data['sheets'] as $sheet ) {
    if ( $sheet['properties']['title'] == $sheet_name ) return $sheet['properties']['sheetId'];
  }
  return null;
}

function delete_sheets(...$sheets) {
  $requests = [];
  foreach ( $sheets as $sheet) {
    $requests[] = [ "deleteSheet" => [ "sheetId" => $sheet]];
  }
  return $this->request(":batchUpdate", ["requests" => $requests]);
  
}


}
