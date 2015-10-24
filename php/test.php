<?php

require __DIR__ . '/restruct.php';

$People	= json_decode( file_get_contents('../people.json') );

function weightClass($w) {
    return $w >= 200 ? '200+' : '0-199';
}

$data	= restruct($People, [
    "= \$this->age > 18 ? 'adults' : 'kids'" => [
        ["= \$this->age > 25", "{first}"]
    ],
    "= \$this->first === 'Travis'?'best':null" => (object)[
	"name" => "{first} {last}",
	"hotness" => "0% *******-------------------------- 100%"
    ],
    "= \$this->first !== 'Travis'?'losers':null" => [(object)[
	"name" => "{first} {last}",
	"hotness" => "0% *-------------------------------- 100%"
    ]],
    "genders" => (object)[
	".array" => true,
        "< gender" => (object)[
	    ".array" => true,
	    0 => "= \$this->age > 25",
	    1 => "{first}"
	]
    ],
    "= weightClass(\$this->weight)" => (object)[
	"{id}" => (object)[
	    "name" => "{first} {last}",
	    "weight" => "< weight"
	],
	"location" => (object)[
	    ".array" => true,
	    0 => "< age",
	    1 => "< weight"
	]
    ],
    "= \$this->gender==='m'?'male':'female'" => [(object)[
	"{id}" => (object)[
	    "name" => "{first} {last}",
	    "email" => true
	]
    ]]
]);
echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
?>