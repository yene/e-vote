<?php

function getJSON() {
  $DEBUG = false;
  $filename = "abstimmung-11-2019.csv";
  $name = $_GET["name"] ?? "";
  $hash = $_GET["hash"] ?? "";

  if ($DEBUG) {echo "checking if we can clone it\n";}
  $command = 'git clone ../data cloned-data';
  $output = exec($command, $arrayOutput, $returnValue);

  if ($returnValue !== 0) {
    if ($DEBUG) {echo "pulling repo\n";}
    $command = 'git -C cloned-data checkout master; git -C cloned-data pull';
    $output = exec($command, $arrayOutput, $returnValue);
  }

  if (strlen($name) > 0) {
    if ($DEBUG) {echo "getting hash of name \n";}
    $command = 'git -C cloned-data log --author="' . $name . '" --pretty=format:"%H"';
    $output = exec($command, $arrayOutput, $returnValue);
    $hash = $output;
    $shorthash = substr($hash, 0, 7);
    if ($DEBUG) {echo "checking out commit \n";}
    $command = 'git -C cloned-data checkout ' . $hash;
    $output = exec($command, $arrayOutput, $returnValue);
    $fileData = file_get_contents('cloned-data/' . $filename);
    $response = new stdClass();
    $response->result = convertCSV($fileData);
    $response->hash = $hash;
    $response->shorthash = $shorthash;
    $response->name = $name;
    return json_encode($response, JSON_UNESCAPED_UNICODE); //JSON_PRETTY_PRINT
  } else if (strlen($hash) > 0) {
    if ($DEBUG) {echo "checking out commit \n";}
    $command = 'git -C cloned-data checkout ' . $hash;
    $output = exec($command, $arrayOutput, $returnValue);
    $fileData = file_get_contents('cloned-data/' . $filename);
    $response = new stdClass();
    $response->result = convertCSV($fileData);
    $response->hash = $hash;

    $command = 'git -C cloned-data log -1 --pretty=format:\'%h\'';
    $output = exec($command, $arrayOutput, $returnValue);
    $response->shorthash = $output;

    $command = 'git -C cloned-data log -1 --pretty=format:\'%an\'';
    $output = exec($command, $arrayOutput, $returnValue);
    $response->name = $output;
    return json_encode($response, JSON_UNESCAPED_UNICODE); // JSON_PRETTY_PRINT
  }

  return json_encode([]);
}


function convertCSV($csvData){
  $flatArray = array_map("str_getcsv", explode("\n", $csvData));
  // take the first array item to use for the final object's property labels
  $columns = $flatArray[0];
  $obj = [];
  for ($i=1; $i < count($flatArray)-1; $i++){
    $row = new stdClass();
    foreach ($columns as $column_index => $column){
      $row->$column = $flatArray[$i][$column_index];
    }
    $obj[] = $row;
  }
  return $obj;
}
?>
