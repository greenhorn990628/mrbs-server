<?php

declare(strict_types=1);

namespace MRBS;

global $allow_registration_default, $registrant_limit_default, $registrant_limit_enabled_default;
global $registration_opens_default, $registration_opens_enabled_default, $registration_closes_default;
global $registration_closes_enabled_default;

$roomId = intval(ApiHelper::value("room_id"));
$startTime = intval(ApiHelper::value("start_time"));
$endTime = intval(ApiHelper::value("end_time"));
$timezone = ApiHelper::value("timezone");
if (!empty($timezone)) {
  date_default_timezone_set($timezone);
}

$now = time();

if ($now > $startTime) {
  ApiHelper::fail();
  return;
}
if ($startTime >= $endTime && (($endTime - $startTime) % (30 * 60) != 0)) {
  ApiHelper::fail();
  return;
}

$qSQL = "room_id = $roomId and
    (($startTime >= start_time and $startTime < end_time)
    or ($endTime > start_time and $endTime <= end_time)
    or ($startTime <= start_time and $endTime >= end_time))
    ";

$queryOne = DBHelper::one(_tbl("entry"), $qSQL);
if (!empty($queryOne)) {
  ApiHelper::fail();
  return;
}

$result = array();
$result["start_time"] = $startTime;
$result["end_time"] = $endTime;
$result["entry_type"] = 0;
$result["room_id"] = $roomId;
$result["create_by"] = "admin";
$result["name"] = get_vocab("ic_tp_meeting");
$result["book_by"] = "/";
$result["type"] = "I";
$result["status"] = 0;
$result["ical_uid"] = generate_global_uid($result["name"]);
$result["allow_registration"] = $allow_registration_default ? 1 : 0;
$result["registrant_limit"] = $registrant_limit_default;
$result["registrant_limit_enabled"] = $registrant_limit_enabled_default  ? 1 : 0;
$result["registration_opens"] = $registration_opens_default;
$result["registration_opens_enabled"] = $registration_opens_enabled_default  ? 1 : 0;
$result["registration_closes"] = $registration_closes_default;
$result["registration_closes_enabled"] = $registration_closes_enabled_default  ? 1 : 0;
$result["create_source"] = "system";

DBHelper::insert(\MRBS\_tbl("entry"), $result);

ApiHelper::success(array(
  "status" => 0
));
