<?php
declare(strict_types=1);
namespace MRBS;

use MRBS\Form\ElementButton;
use MRBS\Form\ElementFieldset;
use MRBS\Form\ElementImg;
use MRBS\Form\ElementInputImage;
use MRBS\Form\ElementInputSubmit;
use MRBS\Form\FieldInputEmail;
use MRBS\Form\FieldInputNumber;
use MRBS\Form\FieldInputSubmit;
use MRBS\Form\FieldInputText;
use MRBS\Form\FieldSelect;
use MRBS\Form\Form;


require "defaultincludes.inc";
require_once "mrbs_sql.inc";


// Check the CSRF token.
// Only check the token if the page is accessed via a POST request.  Therefore
// this page should not take any action, but only display data.
//Form::checkToken(true);

// Check the user is authorised for this page
//checkAuthorised(this_page());

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$type = $data['type'];
$area = $data['area'];

if ($type == 'all'){

} else if ($type == 'area'){
  $sql = "SELECT * FROM " . _tbl("area");
  $result = db() -> query($sql);
  $response = array(
    "code" => 0,
    "message" => "success",
    "data" => array(

    )
  );
  while($row = $result -> next_row_keyed()){
    unset($row['exchange_server']);
    unset($row['wxwork_corpid']);
    unset($row['wxwork_secret']);
    $response["data"][] = $row;
  }
  echo json_encode($response);
}else if ($type == 'room'){
  if (!empty($area)){
    $areaExist = db() -> query("SELECT id FROM " . _tbl("area") . " WHERE id = ?", array($area));
    if ($areaExist -> count() == 0){
      $response = array(
        "code" => -3,
        "message" => "area not exist"
      );
      echo json_encode($response);
    }
    $sql = "SELECT *  FROM " . _tbl("room") . " WHERE area_id = ?";
    $result = db() -> query($sql, array($area));
    if ($result -> count() === 0){
      $response = array(
        "code" => -4,
        "message" => "there is no room in the area"
      );
      echo json_encode($response);
    }else{
      $response = array(
        "code" => 0,
        "message" => "success",
        "data" => array()
      );
      while($row = $result -> next_row_keyed()){
        unset($row['exchange_username']);
        unset($row['exchange_password']);
        unset($row['wxwork_mr_id']);
        unset($row['exchange_sync_state']);
        $response["data"][] = $row;
      }
      echo json_encode($response);
    }
  }else{
    $result = db() -> query("SELECT * FROM " . _tbl("room"));
    if ($result -> count() === 0){
      $response = array(
        "code" => -3,
        "message" => "room not exist"
      );
      echo json_encode($response);
      return;
    }
    $rows = $result -> all_rows_keyed();
    foreach($rows as $row){
      unset($row['exchange_username']);
      unset($row['exchange_password']);
      unset($row['wxwork_mr_id']);
      unset($row['exchange_sync_state']);
      $ans[] = $row;
    }
    $response = array(
      "code" => 0,
      "message" => "success",
      "data" => $ans
    );
    echo json_encode($response);
    return;
  }
}else{
  $response = array(
    "code" => -1,
    "message" => "invalid type",
  );
  echo json_encode($response);
}


