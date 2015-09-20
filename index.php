#!/usr/bin/php -q
<?php
include './File/Gettext.php';
include './File/Gettext/PO.php';

$sourcesDir = '/home/vitex/Projects/VitexSoftware/iciedit/src/';
$localesDir = '/home/vitex/Projects/VitexSoftware/iciedit/src/locale';



$gt = new File_Gettext_PO();
$gt->load($localesDir . '/en_US/LC_MESSAGES/messages.po');
$englishCount = count($gt->strings);

function read_stdin()
{
    $fr = fopen("php://stdin", "r");   // open our file pointer to read from stdin
    $input = fgets($fr, 128);        // read a maximum of 128 characters
    $input = rtrim($input);         // trim any trailing spaces.
    fclose($fr);                   // close the file handle
    return $input;                  // return the text entered
}

function exchLocStrings($filepath, $gt)
{
    $locs = array();


    if (substr($filepath, -4, 4) != '.php') {
        return null;
    }

    $in = implode('', file($filepath));

    preg_match_all('/[^\'\)]*/', $in, $out);

    foreach ($out[0] as $pos => $locstr) {
        if (!strlen(trim($locstr))) {
            continue;
        }
        if (substr($locstr, -2, 2) == '_(') {
            $locs[] = $out[0][$pos + 2];
        }
    }

    $stringCount = count($locs);

    echo "### $filepath : k přeložení $stringCount \n";

    foreach ($locs as $localstring) {
        if (array_key_exists($localstring, $gt->strings)) {
            echo "Přehazuji: $localstring za " . $gt->strings[$localstring] . "\n";
        } else {
            echo "Chybějící lokalizace: $localstring\n";
            $English = $line = fgets(STDIN);
            if (!strlen(trim($English))) {
                $gt->strings[$localstring] = 'Please Translate to English: ' . $localstring;
            } else {
                $gt->strings[$localstring] = $English;
            }
        }

        $in = str_replace($localstring, $gt->strings[$localstring], $in);
        echo "Zbyva přeložit " . $stringCount-- . "\n";
    }

    $newdest = dirname($filepath) . '2';
    mkdir($newdest, '0777', true);
    file_put_contents($newdest . basename($newdest), $in);
}

$sources = scandir($sourcesDir, 1);
$sources2 = scandir($sourcesDir . '/classes/', 1);
foreach ($sources2 as $classdir) {
    $sources[] = 'classes/' . $classdir;
}

foreach ($sources as $id => $filename) {
    if (preg_match('/^.*\.(php)$/i', $filename)) {
        echo $id . ' z ' . count($sources) . ' souborů ' . "\n";
        exchLocStrings($sourcesDir . '/' . $filename, $gt);
    }
}

//exchLocStrings('/home/vitex/Projects/VitexSoftware/iciedit/src/index.php');
$gt->strings = array_flip($gt->strings);
mkdir($localesDir . '2/cs_CZ/LC_MESSAGES/', '0777', true);
$gt->save($localesDir . '2/cs_CZ/LC_MESSAGES/messages.po2');

$gt2 = new File_Gettext_PO();
$gt2->load($localesDir . '/en_US/LC_MESSAGES/messages.po');

$gt2->strings = array();

foreach ($gt->strings as $strId => $string) {
    $gt2->strings[$strId] = $strId;
}
mkdir($localesDir . '2/en_US/LC_MESSAGES/', '0777', true);
$gt->save($localesDir . '2/en_US/LC_MESSAGES/messages.po');


