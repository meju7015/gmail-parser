<?php

namespace App\Http\Controllers;

use App\Services\GoogleClient;
use App\Services\GmailParser;
use Carbon\Carbon;
use Illuminate\Support\Str;

class GmailClientController extends Controller
{
    function getGmailClient()
    {

        $client = new GoogleClient('me', '6ohc7reoj9nbp5jccs2ldkknl4@group.calendar.google.com');

        $parser = new GmailParser(
            $client,
            '17824e03936aa990'
        );

        dump($client->getThreads());

        dump($parser->parseThreadToMessageHeader());
        dump($parser->parseMessageBodyToFormat());

        $sendData = $parser->parseMessageBodyToFormat();
        $googleDate = new \Google_Service_Calendar_EventDateTime();


        $client->createEvent([
            'summary' => $sendData['job'],
            'description' => $sendData['issue'],
            'start' => [
                'dateTime' => Carbon::make($sendData['work_date'][0])->format('Y-m-d\TH:i:s'),
                'timeZone' => 'America/Los_Angeles'
            ],
            'end' => [
                'dateTime' => Carbon::make($sendData['work_date'][1])->format('Y-m-d\TH:i:s'),
                'timeZone' => 'America/Los_Angeles'
            ]
        ]);
    }
}
