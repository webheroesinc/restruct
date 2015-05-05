<?php

#
# ReStruct -- Turn SQL results into beautiful JSON structures.
#
# Copyright (c) 2015, Web Heroes Inc..
#
# ReStruct is free software: you can redistribute it and/or modify it under the
# terms of the GNU General Public License as published by the Free Software
# Foundation, either version 3 of the License, or (at your option) any later
# version.  See the LICENSE file at the top of the source tree.
#
# ReStruct is distributed in the hope that it will be useful, but WITHOUT ANY
# WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
# A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
#

function __startsWith($haystack, $needle) {
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}
function __format($str, $dict) {
    $dict = (array)$dict;
    $str = preg_replace_callback('#\{\}#', function($r){
        static $i = 0;
        return '{'.($i++).'}';
    }, $str);

    return str_replace(
        array_map(function($k) {
            return '{'.$k.'}';
        }, array_keys($dict)),
        array_values($dict),
        $str
    );
}
function __fill($s, $data) {
    if (__startsWith( $s, ":<" )) {
        $k		= trim(substr($s, 2));
        return $data->$k;
    }
    $v			= __format($s, $data);
    if (__startsWith( $s, ":" )) {
        $e		= trim(substr($v, 1));
        eval("\$v = $e;");
    }
    return $v;
}

function Attach($data, $structure) {

    if (is_array($data))
        return __attach_list($data, $structure);
    
    $struct		= clone $structure;
    foreach ($struct as $k => $v) {
        if ($v === true)
            $struct->$k	= $data->$k;
        else if ($v === false)
            unset( $struct->$k );
        else if (is_object($v))
            $struct->$k	= Attach($data, $v);
        else if (is_string($v))
            $struct->$k	= __fill($v, $data);
        else
            $struct->$k	= NULL;
    }
    return $struct;
}

function __attach_list($data, $structure) {
    $K			= ".key";
    if (isset( $structure->$K))
        return __attach_group($data, $structure);
    
    $result		= [];
    foreach ($data as $key => $datum) {
        $result[]	= Attach($datum, $structure);
    }
    return $result;
}

function __extract_struct($path, $struct) {
    $segments		= explode( '->', $path );
    for ($i=0; $i < (count($segments)-1); $i++) {
        $seg		= $segments[$i];
        $struct		= $struct->$seg;
    }
    $seg		= $segments[$i];
    $s			= clone $struct->$seg;
    unset( $struct->$seg );
    return $s;
}

function __path_assign($path, $d1, $d2) {
    $segments		= explode( '->', $path );
    for ($i=0; $i < (count($segments)-1); $i++) {
        $seg		= $segments[$i];
        $d1		= $d1->$seg;
    }
    $seg		= $segments[$i];
    $d1->$seg		= $d2;
    return $d1;
}

function __attach_group($data, $structure) {
    $K			= ".key";
    $I			= ".index";
    $S			= ".single";
    $gstruct		= clone $structure;
    $gkey		= $gstruct->$K;
    $gindex		= isset( $structure->$I ) ? $structure->$I : true;
    $gsingle		= isset( $structure->$S ) ? $structure->$S : false;
    $sub_structs	= [];
    $duplicates		= [];
    
    unset( $gstruct->$K );
    unset( $gstruct->$I );
    unset( $gstruct->$S );

    if (is_array($gkey)) {
        $duplicates	= $gkey;
        $gkey		= $duplicates[0];
        unset($duplicates[0]);
    }
    foreach ($duplicates as $k) {
        $sub_structs[$k]	= __extract_struct($k, $gstruct);
    }

    $groups		= __group_data($data, $gkey);
    $gresult		= [];
    foreach ($groups as $key => $rows) {
        $gresult[$key]	= Attach( $rows[0], $gstruct );
        foreach ($duplicates as $k) {
            __path_assign($k, $gresult[$key], Attach( $rows, $sub_structs[$k] ));
        }
    }
    if ($gsingle === true) {
        foreach ($gresult as $k => $d) {
            $gresult	= $d;
            break;
        }
    }
    else if ($gindex === false) {
        $garray		= [];
        foreach ($gresult as $k => $d) {
            $garray[]	= $d;
            
        }
        $gresult	= $garray;
    }
    
    return $gresult;
}
function __group_data($data, $key) {
    $groups		= [];
    foreach ($data as $d) {
        $k		= __fill($key, $d);
        if (!isset($groups[$k]))
            $groups[$k]	= [];
        $groups[$k][]	= $d;
    }
    return $groups;
}

?>