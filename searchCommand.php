<?php

ini_set('max_execution_time', '1700');
set_time_limit(1700);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Content-Type: application/json; charset=utf-8');
http_response_code(200);

function send_bearer($url, $token, $type = "GET", $param = []){
  $descriptor = curl_init($url);
  curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
  curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($descriptor, CURLOPT_HTTPHEADER, array("User-Agent: M-Soft Integration", "Content-Type: application/json", "Authorization: Bearer ".$token)); 
  curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);
  $itog = curl_exec($descriptor);
  curl_close($descriptor);
  return $itog;
}

if ($input["ssToken"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'ssToken' is missing";
}
if ($input["userId"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'userId' is missing";
}
if ($input["search"] == NULL) {
  $result["state"] = false;
  $result["error"]["message"][] = "'search' is missing";
} else if (!is_array($input["search"])){
  $result["state"] = false;
  $result["error"]["message"][] = "'search' must be an array";
}
if ($input["delimiter"] == NULL) {
  $input["delimiter"] = "_";
}
if ($input["maxMessage"] == NULL) {
  $input["maxMessage"] = 20;
}
if ($result["state"] == false) {
  echo json_encode($result);
  exit;
}

$ps = 2; $c=1;
for ($p = 1; $p<$ps; $p++) {
  $getMessages = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$input["userId"]."/messages?limitation=20&page=".$p, $input["ssToken"]), true);
  if ($getMessages["collection"] != NULL) {
    $ps = $getMessages["cursor"]["pages"];
    foreach ($getMessages["collection"] as $oneMessage) {
      if ($oneMessage["content"]["type"] != "text" || $oneMessage["sender"]["type"] != "contact") {
        $c++;
        continue;
      }
      foreach ($input["search"] as $oneSearch) {
        if (mb_stripos($oneMessage["content"]["resource"]["parameters"]["content"], $oneSearch) !== false) {
          $result["response"] = [
            "message" => $oneMessage["content"]["resource"]["parameters"]["content"],
            "needle" => $oneSearch,
            "array" => explode($input["delimiter"], $oneMessage["content"]["resource"]["parameters"]["content"]),
            "code" => explode($input["delimiter"], $oneMessage["content"]["resource"]["parameters"]["content"], 2)[1],
          ];
          if ($input["delete"] === true) {
            $result["delete"] = json_decode(send_bearer("https://api.smartsender.com/v1/gates/".$oneMessage["gate"]["id"]."/messages", $input["ssToken"], "DELETE", ["messageIds"=>[$oneMessage["id"]]]), true);
          }
          break 3;
        }
      }
      if ($c>=$input["maxMessage"]) {
        $result["state"] = false;
        $result["error"]["message"][] = "max messages";
        break 2;
      }
      $c++;
    }
  } else {
    $result["state"] = false;
    $result["error"]["message"][] = "failed load messages";
    $result["error"]["SmartSender"] = $getMessages;
  }
}

$result["stat"] = [
  "pages" => $p,
  "messages" => $c,
];

echo json_encode($result, JSON_UNESCAPED_UNICODE);