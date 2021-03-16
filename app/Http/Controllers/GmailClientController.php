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
        $origin = $client->getThreads(5)->getThreads();
        $next = $origin;

        if ($prev === null) {
            $client->setCacheInbox($next);
            $prev = $client->getCacheInbox();
        }

        Log::info('******** 스케쥴링 시작 ********');

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

                Log::info(`{$body['job']} :: 캘린더에 추가됨. - `.Carbon::now()->format('Y-m-d H:i:s'));
            }
        }

        $client->setCacheInbox($origin);
        Log::info('******** 스케쥴링 끝 ********');
    }
}
