<?php

require __DIR__ . '/restruct.php';

$People	= json_decode( file_get_contents('../people.json') );
$struct	= json_decode( file_get_contents('./struct.json') );

function weightClass($w) {
    return $w >= 200 ? '200+' : '0-199';
}
    
// echo json_encode($struct, JSON_PRETTY_PRINT) . "\n";

$data	= restruct($People, $struct);
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";

?>