<?php

require_once('header.php');

require_once('gamesearch/search.php');

header('Content-type: application/json');
header("Access-Control-Allow-Origin: *");

$search = new search('My games');

$search->printGamesListJSON();

?>