<?php

namespace App\Console\Commands;

use App\Services\GmailParser;
use App\Services\GoogleClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GmailConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gmail:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $client = new GoogleClient('me', '6ohc7reoj9nbp5jccs2ldkknl4@group.calendar.google.com');

        $prev = $client->getCacheInbox();
        $next = $client->flatArray($client->getThreads(5)->getThreads());

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

        $client->setCacheInbox($next);
        Log::info('******** 스케쥴링 끝 ********');
    }
}
