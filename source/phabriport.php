<?php

require_once 'libphutil/src/__phutil_library_init__.php';

date_default_timezone_set('Europe/Copenhagen');

$config = parse_ini_file('config.ini');

// Conduit API: http://domain.com/conduit/method/differential.query/
$api_parameters = [
    'authors'   => [$config['phid_user']],
    'order'     => 'order-modified',
];

$client = (new ConduitClient($config['url']))->setConduitToken($config['api_token']);
$diffs = $client->callMethodSynchronous('differential.query', $api_parameters);

$currentDate = new DateTime();

// Subject of the email
$output['subject'] = sprintf(
    'PT Status report Week %s, %s (%s)',
    $currentDate->format("W"),
    $currentDate->format("Y"),
    explode(' ', $config['name'])[0]
);

// Body of the email
foreach ($diffs as $key => $diff)
{
    $dateModified = new DateTime(date("Y-m-d H:i:s", $diff['dateModified']));
    $lastWeek = new DateTime(date("Y-m-d H:i:s", strval(strtotime('-' . $config['days'] . ' day'))));

    if ($dateModified > $lastWeek)
    {
        switch ($diff['status'])
        {
            // Needs Review
            case 0:
                $output['ongoing'][] = "#" . $diff['id'] . " - " . $diff['title'] . "\n";
                break;

            // Accepted
            case 2:
                $output['completed'][] = "#" . $diff['id'] . " - " . $diff['title'] . "\n";
                break;

            // Closed
            case 3:
                $output['completed'][] = "#" . $diff['id'] . " - " . $diff['title'] . "\n";
                break;

            default:
                $output['all'] = "#" . $diff['id'] . " - " . $diff['title'];
                break;
        }
    }
}

// Return string
echo sprintf(
    "%s|Issues: none\n\nRisks: none\n\nCompleted:\n%s\nOngoing:\n%s\n\nPlaned:",
    $output['subject'],
    implode("", $output['completed']),
    implode("", $output['ongoing'])
);
