<?php
declare(strict_types=1);

namespace MRBS;

require "defaultincludes.inc";
require_once "mrbs_sql.inc";

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$area = null;

$type = $data['type'];
$id = $data['id'];


if ($type == 'all'){
  $result = db() -> query("SELECT R.id as room_id, R.disabled as room_disabled, A.disabled as area_disabled, R.*, A.* FROM " . _tbl("room") . " R LEFT JOIN " . _tbl("area") . " A ON R.area_id = A.id");
  if ($result -> count() < 1){
    ApiHelper::fail(get_vocab("room_not_exist"), ApiHelper::ROOM_NOT_EXIST);
  }
  $rows = $result -> all_rows_keyed();
  foreach ($rows as $row){
    $areaId = $row['area_id'];
    $roomId = $row['room_id'];
    $roomName = $row['room_name'];
    $areaName = $row['area_name'];
    if (!isset($tmp[$areaId])){
      $tmp[$areaId] = array(
        'area_id' => $areaId,
        'area_name' => $areaName,
        'disabled' => $row['area_disabled'],
        'start_time' => sprintf("%02d", $row['morningstarts'] > 12 ? $row['morningstarts'] - 12 : $row['morningstarts']) . ":" . sprintf("%02d", $row['morningstarts_minutes']) . ($row['morningstarts'] > 12 ? " PM" : " AM"),
        'end_time' => sprintf("%02d", $row['eveningends'] > 12 ? $row['eveningends'] - 12 : $row['eveningends']) . ":" . sprintf("%02d", $row['eveningends_minutes']) . ($row['eveningends'] > 12 ? " PM" : " AM"),
        'rooms' => array()
      );
    }

    if (!isset($tmp[$areaId]['rooms'][$roomId])){
      $tmp[$areaId]['rooms'][$roomId] = array(
        'room_id' => $roomId,
        'room_name' => $roomName,
        'description' => $row['description'],
        'status' => "可预约",
        'disabled' => $row['area_disabled'] == 1 ? 1 : $row['room_disabled']
      );
    }
  }
  $result = array(
    'areas' => array_values($tmp)
  );
  foreach ($result['areas'] as &$area) {
    $area['rooms'] = array_values($area['rooms']);
  }
  ApiHelper::success($result);
}else if ($type == 'area'){
  $result = db() -> query("SELECT R.id as room_id,R.disabled as room_disabled, A.disabled as area_disabled, R.*, A.* FROM " . _tbl("room") . " R LEFT JOIN " . _tbl("area") . " A ON R.area_id = A.id WHERE A.id = ?", array($id));
  if ($result -> count() < 1){
    ApiHelper::fail(get_vocab("room_not_exist"), ApiHelper::ROOM_NOT_EXIST);
  }
  $rows = $result -> all_rows_keyed();
  foreach ($rows as $row){
    if (!isset($area)){
      $area = array(
        'area_id' => $row['area_id'],
        'disabled' => $row['area_disabled'],
        'area_name' => $row['area_name'],
        'rooms' => array()
      );
    }
    $area['rooms'][] = array(
      'room_id' => $row['room_id'],
      'disabled' => $row['area_disabled'] == 1 ? 1 : $row['room_disabled'],
      'room_name' => $row['room_name']
    );
  }
  $data1['areas'] = $area;
  ApiHelper::success($data1);
}else if($type == 'room'){
  $result = db() -> query("SELECT R.id as room_id, R.disabled as room_disabled, A.disabled as area_disabled, R.*, A.* FROM " . _tbl("room") . " R LEFT JOIN " . _tbl("area") . " ON R.area_id = A.id WHERE R.id = ?", array($id));
  if ($result -> count() != 1){
    ApiHelper::fail(get_vocab("room_not_exist"), ApiHelper::ROOM_NOT_EXIST);
  }
  $row = $result -> next_row_keyed();
  $area = array(
    'area_id' => $row['area_id'],
    'area_name' => $row['area_name'],
    'disabled' => $row['area_disabled'],
    'rooms' => array()
  );
  $area['rooms'][] = array(
    'room_id' => $row['room_id'],
    'disabled' => $row['area_disabled'] == 1 ? 1 : $row['room_disabled'],
    'room_name' => $row['room_name']
  );
  ApiHelper::success($area);
}else{
  ApiHelper::fail(get_vocab("invalid_types"), ApiHelper::INVALID_TYPES);
}
