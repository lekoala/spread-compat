<?php

use LeKoala\SpreadCompat\Xlsx\Native;
use Shuchkin\SimpleXLSXGen;

require './vendor/autoload.php';

error_log(-1);

$native = new Native();
$data = $native->readFile(__DIR__ . '/tests/data/header.xlsx');
// var_dump(iterator_to_array($data));

$native = new Native();
$data = $native->readFile(__DIR__ . '/tests/data/header.xlsx', assoc: true);
// var_dump(iterator_to_array($data));


$books = [
    ['ISBN', 'title', 'author', 'publisher', 'ctry', 'date', 'raw'],
    [618260307, 'The Hobbit 00', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA', '2020-05-20', "\0" . '2020-10-04 16:02:00'],
    [908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ', '2022-08-20', "\0" . '2020-10-04 16:02:00']
];
$xlsx = SimpleXLSXGen::fromArray($books);
// $xlsx->saveAs(__DIR__ . '/.dev/books.xlsx');

// Yes, you can stream the response directly
$native = new Native();
$native->output($books, 'books.xlsx');
