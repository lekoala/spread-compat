<?php

use LeKoala\SpreadCompat\Xlsx\Native;
use Shuchkin\SimpleXLSXGen;

require dirname(__DIR__) . '/vendor/autoload.php';
require dirname(__DIR__) . '/res/F.php';

use LeKoala\F;

error_log(-1);

$native = new Native();
$data = $native->readFile(dirname(__DIR__) . '/tests/data/header.xlsx');
// var_dump(iterator_to_array($data));

$native = new Native();
$data = $native->readFile(dirname(__DIR__) . '/tests/data/header.xlsx', assoc: true);
// var_dump(iterator_to_array($data));


$books = [
    ['ISBN', 'title', 'author', 'publisher', 'ctry', 'date', 'raw'],
    [618260307, 'The Hobbit 00', 'J. R. R. Tolkien', 'Houghton Mifflin', 'USA', '2020-05-20', "\0" . '2020-10-04 16:02:00'],
    [908606664, 'Slinky Malinki', 'Lynley Dodd', 'Mallinson Rendel', 'NZ', '2022-08-20', "\0" . '2020-10-04 16:02:00']
];
$xlsx = SimpleXLSXGen::fromArray($books);
// $xlsx->saveAs(__DIR__ . '/.dev/books.xlsx');

// Short style faker data


function gen($max = 100_000)
{
    $i = 0;
    while ($i < $max) {
        $i++;
        yield [
            $i,
            F::d(),
            F::dt(),
            F::i(10_000, 30_000),
            $fn = F::fn(),
            $sn = F::sn(),
            F::em($fn . $sn),
            F::uw(5, 10),
            F::addr(),
            $ctry = F::ctry(),
            F::l($ctry),
            F::b(),
            F::pick('1', ''),
            F::m(),
        ];
    }
}

// Yes, you can stream the response directly
// Even if it has 1 million rows and that it creates a file of 97 mb...
$native = new Native();
$native->output(gen(), 'books.xlsx');
