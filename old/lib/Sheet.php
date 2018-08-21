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

function request_delete_sheet($sheetId) {
  return [ 'deleteSheet' => ['sheetId' => $sheetId] ];
}

function request_add_sheet($title, $sheetId = null) {
  $r = [
    'addSheet' => [
      'properties' => [
        'title' => $title,
        'gridProperties' => [ "frozenRowCount" => 1 ]
      ]
    ]
  ];

  if ( isset($sheetId) ) $r['addSheet']['properties']['sheetId'] = $sheetId;
  return $r;
}

function request_table_headers($sheet, $headers) {
  $values = [];
  foreach ( $headers as $header) {
    $values[] = [
      "userEnteredFormat" => [
        "numberFormat" => [ "type" => "TEXT" ],
        "backgroundColor"=> [ "red" => 0.0, "green" => 0.0, "blue" => 0.0 ],
        "horizontalAlignment" => "CENTER",
        "textFormat"=> [
          "foregroundColor" => [ "red"=> 1.0, "green" => 1.0, "blue" => 1.0 ],
          "fontSize" => 12,
          "bold" => true
        ]
      ],
      "userEnteredValue" => [
        "stringValue" => $header
      ]
    ];
  }

  return [
    "updateCells" => [
      "rows" => [["values" => $values]],
      "fields" => "userEnteredFormat(backgroundColor,textFormat,horizontalAlignment,numberFormat),userEnteredValue.stringValue",
      "range" => self::range(['sheetId' => $sheet, "startRowIndex" => 0, "endRowIndex" => 1, "startColumnIndex" => 0, "endColumnIndex" => count($headers)])
    ]
  ];
}

function request_table_data($sheet, $data) {
  $values = [];
  foreach ( $data as $row ) {
    $value_row = [];
    foreach ( $row as $key => $column ) {
      $value_row[] = [
        "userEnteredValue" => self::user_entered_value($column, $key)
      ];
    }
    $values[] = [ "values" => $value_row ];
  }

  return [
    'updateCells' => [
      "rows" => $values,
      "fields" => "userEnteredValue",
      'range' => self::range(['sheetId' => $sheet, "startRowIndex" => 1, "endRowIndex" => 1 + count($data), "startColumnIndex" => 0, 'endColumnIndex' => count(array_values($data)[0] ?? [])])
    ]
  ];
}

function request_clear($sheet) {
  return [
    "updateCells" => [
      "range" => self::range(['sheetId' => $sheet]),
      "fields" => "*"
    ]
  ];
}

function request_update_title($sheet, $title) {
  return [
    "updateSheetProperties" => [
      "properties" => [
        "sheetId" => $sheet,
        "title" => $title
      ],
      "fields" => "title"
    ]
  ];
}

function clear_all_sheets() {
  $data = $this->request();
  $request = [$this->request_add_sheet('Dummy',999)];
  foreach ( $data['sheets'] as $sheet ) {
    $request[] = $this->request_delete_sheet($sheet['properties']['sheetId']);
  }
  $this->batch_update($request);
}

function rename_sheet($sheet, $title) {
  return $this->request(":batchUpdate", [
    "requests" => [
      [
        "updateSheetProperties" => [
          "properties" => [
            "sheetId" => $sheet,
            "title" => $title,
            "index" => 0
          ],
          "fields" => "title,index"
        ]
      ]
    ]
  ]);
}

function batch_update(...$requests) {
  $response = $this->request(":batchUpdate", [ "requests" => $requests ]);
  if ( isset($response['error']) ) return $response;
  else return $response['replies'];
}


function create_sheet($title) {
  $response = $this->request(":batchUpdate", [
    "requests" => [
      [
        "addSheet" => [ "properties" => [ "title" => $title ]]
      ]
    ]
  ]);
  if ( isset($response['error']) ) return $response;
  else return $response['replies'][0]['addSheet'];

}

function request_column_chart($dest_sheet, $src_sheet, $title, $rows, $columns) {
  
  $source = function($column) use ($src_sheet, $rows) {
    return [
      "sources" => [
        "sheetId" => $src_sheet,
        "startColumnIndex" => $column,
        "endColumnIndex" => $column + 1,
        "startRowIndex" => 0,
        "endRowIndex" => count($rows) + 1
      ]
    ];
  };

  $series = [];
 
  for ( $i = $columns[0] ; $i <= $columns[1] ; $i ++ ) {
    $series[] = [
      'series' => [ "sourceRange" => $source($i) ],
      'targetAxis' => "LEFT_AXIS"
    ];
  }


  return [
    "addChart" => [
      "chart" => [
        "position" => ["sheetId" => $dest_sheet ],
        "spec" => [
          "title" => $title,
          "hiddenDimensionStrategy"=> "SKIP_HIDDEN_ROWS_AND_COLUMNS",
          "basicChart" => [
            "chartType" => "COLUMN",
            "axis" => [["position" => "BOTTOM_AXIS"], ["position" => "LEFT_AXIS"]],
            "domains" => [[ "domain" => [ "sourceRange" => $source(0) ]]],
            "series" => $series,
            "headerCount" => 1
          ]
        ]
      ]
    ]
  ];
}

function request_pie_chart($dest_sheet, $src_sheet, $title, $rows, $col) {

  $source = function($column) use ($src_sheet, $rows) {
    return [
      "sources" => [
        "sheetId" => $src_sheet,
        "startColumnIndex" => $column,
        "endColumnIndex" => $column + 1,
        "startRowIndex" => 0,
        "endRowIndex" => count($rows) + 1
      ]
    ];
  };

  return [
    "addChart" => [
      "chart" => [
        "position" => ["sheetId" => $dest_sheet ],
        "spec" => [
          "title" => $title,
          "pieChart" => [
            "domain" => [ "sourceRange" => $source(0) ],
            "series" => [ 'sourceRange' => $source($col) ]
          ]
        ]
      ]
    ]
  ];
}

static function create_empty_table_array($row_headers, $col_headers, $default_value = 0) {
  $table = [];
  foreach ( $row_headers as $r ) {
    $current = ['name' => $r];
    foreach ( $col_headers as $c ) {
      $current[$c] = $default_value;
    }
    $table[$r] = $current;
  }
  return $table;
}


}
