<?php

namespace App\Http\Controllers;

use App\Services\GoogleClient;
use App\Services\GmailParser;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class GmailClientController extends Controller
{
    function getGmailClient()
    {

        $client = new GoogleClient('me', '6ohc7reoj9nbp5jccs2ldkknl4@group.calendar.google.com');

        $prev = $client->getCacheInbox();
        $next = $client->flatArray($client->getThreads(5)->getThreads());

        if ($prev === null) {
            $client->setCacheInbox($next);
            $prev = $client->getCacheInbox();
        }

        foreach ($prev as $pk => $pi) {
            foreach ($next as $ni) {
                if ($pi->id === $ni->id) {
                    unset($prev[$pk]);
                }
            }
        }

        foreach ($prev as $key => $item) {
            $parser = new GmailParser(
                $client,
                $item->id
            );

            $body = $parser->parseMessageBodyToFormat();

            if (!empty($body['job'])) {
                $client->createEvent([
                    'summary' => $body['job'],
                    'description' => $body['issue'],
                    'start' => [
                        'dateTime' => Carbon::make($body['work_date'][0])->format('Y-m-d\TH:i:s'),
                        'timeZone' => 'America/Los_Angeles'
                    ],
                    'end' => [
                        'dateTime' => Carbon::make($body['work_date'][1])->format('Y-m-d\TH:i:s'),
                        'timeZone' => 'America/Los_Angeles'
                    ]
                ]);
            }
        }
    }
}
