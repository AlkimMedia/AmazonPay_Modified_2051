<?php

require_once __DIR__.'/vendor/autoload.php';

if(!empty($_SESSION['language'])){
    require_once __DIR__.'/lang/'.$_SESSION['language'].'.php';
}

//TODO replace
foreach(glob(__DIR__.'/classes/Helpers/*.php') as $_file){
    require_once $_file;
}

foreach(glob(__DIR__.'/classes/Models/*.php') as $_file){
    require_once $_file;
}

foreach(glob(__DIR__.'/classes/Struct/*.php') as $_file){
    require_once $_file;
}

