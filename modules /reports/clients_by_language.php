<?php

// Developed by Host Media Ltd
// https://hostmedia.uk
// Version 1.0.0

use WHMCS\Database\Capsule;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

$reportdata["title"] = "Clients by Language";
$reportdata["description"] = "This report shows the distribution of clients by their selected language preference.";

$reportdata["tableheadings"] = array("Language", "Number of Clients", "Percentage");

// Get the default language from WHMCS configuration
$defaultLanguage = Capsule::table('tblconfiguration')
    ->where('setting', '=', 'Language')
    ->value('value');

// Get all language preferences from clients
$results = Capsule::table('tblclients')
    ->select(Capsule::raw('language, count(*) as `count`'))
    ->where('Status', '=', 'Active')
    ->groupBy('language')
    ->orderBy('count', 'desc')
    ->get()
    ->all();

$totalClients = 0;
$languageStats = array();

// Calculate total clients and prepare data
foreach ($results as $result) {
    $totalClients += $result->count;
    $languageStats[$result->language] = $result->count;
}

// Sort languages by count in descending order
arsort($languageStats);

// Prepare data for table and chart
$chartdata = array();
$chartdata['cols'][] = array('label' => 'Language', 'type' => 'string');
$chartdata['cols'][] = array('label' => 'Clients', 'type' => 'number');

foreach ($languageStats as $language => $count) {
    $percentage = round(($count / $totalClients) * 100, 2);
    $displayLanguage = $language ?: "Default ($defaultLanguage)";
    $reportdata["tablevalues"][] = array(
        $displayLanguage,
        $count,
        $percentage . '%'
    );

    $chartdata['rows'][] = array(
        'c' => array(
            array('v' => $displayLanguage),
            array('v' => $count)
        )
    );
}

$args = array();
$args['legendpos'] = 'right';
$args['height'] = '600px';
$args['width'] = '100%';
$args['chartArea'] = array('width' => '80%', 'height' => '80%');

$reportdata["headertext"] = $chart->drawChart('Pie', $chartdata, $args, '600px');
