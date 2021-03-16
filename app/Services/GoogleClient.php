<?php

namespace App\Services;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Calendar;
use Illuminate\Support\Facades\Redis;

class GoogleClient
{
    private $client;

    private $service;

    private $user;

    private $calendarID;

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __construct(...$info)
    {
        $this->service = new GoogleService();

        [$user, $calendarID] = $info;
        $this->setClient();
        $this->user = $user;
        $this->calendarID = $calendarID;

        $this->service->gmail = $this->setGmailService();
        $this->service->calendar = $this->setCalendarService();

    }

    public function createEvent($event)
    {
        /**
         * $event = new Google_Service_Calendar_Event(array(
         * 'summary' => 'Google I/O 2015',
         * 'location' => '800 Howard St., San Francisco, CA 94103',
         * 'description' => 'A chance to hear more about Google\'s developer products.',
         * 'start' => array(
         * 'dateTime' => '2015-05-28T09:00:00-07:00',
         * 'timeZone' => 'America/Los_Angeles',
         * ),
         * 'end' => array(
         * 'dateTime' => '2015-05-28T17:00:00-07:00',
         * 'timeZone' => 'America/Los_Angeles',
         * ),
         * 'recurrence' => array(
         * 'RRULE:FREQ=DAILY;COUNT=2'
         * ),
         * 'attendees' => array(
         * array('email' => 'lpage@example.com'),
         * array('email' => 'sbrin@example.com'),
         * ),
         * 'reminders' => array(
         * 'useDefault' => FALSE,
         * 'overrides' => array(
         * array('method' => 'email', 'minutes' => 24 * 60),
         * array('method' => 'popup', 'minutes' => 10),
         * ),
         * ),
         * ));
         */
        $cal = $this->service->calendar;
        $event = $cal->events->insert($this->calendarID, new \Google_Service_Calendar_Event($event));
        return $event;
    }

    public function setClient(Google_Client $client = null): void
    {
        if ($client === null) {
            $client = new Google_Client();
            $client->setApplicationName('Quickstart');
            $client->setScopes([Google_Service_Calendar::CALENDAR, Google_Service_Gmail::GMAIL_READONLY]);
            $client->setAuthConfig(base_path() . '/credentials.json');
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            $tokenPath = base_path() . '/_token.json';
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);
            }

            if ($client->isAccessTokenExpired()) {
                if ($client->getRefreshToken()) {
                    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                } else {
                    $authUrl = $client->createAuthUrl();
                    printf("Open the following link in your browser:\n%s\n", $authUrl);
                    print 'Enter verification code: ';
                    $authCode = trim(fgets(STDIN));

                    // Exchange authorization code for an access token.
                    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                    $client->setAccessToken($accessToken);

                    // Check to see if there was an error.
                    if (array_key_exists('error', $accessToken)) {
                        throw new Exception(join(', ', $accessToken));
                    }
                }

                if (!file_exists(dirname($tokenPath))) {
                    mkdir(dirname($tokenPath), 0700, true);
                }
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }
        }

        $this->client = $client;
    }

    public function setGmailService(\Google_Service_Gmail $service = null): Google_Service_Gmail
    {
        if ($service === null) {
            $service = new Google_Service_Gmail($this->client);
        }

        return $service;
    }

    public function setCalendarService(\Google_Service_Calendar $service = null): Google_Service_Calendar
    {
        if ($service === null) {
            $service = new Google_Service_Calendar($this->client);
        }

        return $service;
    }

    public function getThreads($length)
    {
        return $this->service->gmail->users_threads->listUsersThreads($this->user, ['maxResults' => $length]);
    }

    public function getMessages()
    {
        return $this->service->gmail->users_messages->listUsersMessages($this->user);
    }

    public function getThread($id)
    {
        return $this->service->gmail->users_threads->get($this->user, $id);
    }

    public function getMessage($id)
    {
        return $this->service->gmail->users_messages->get($this->user, $id);
    }

    public function flatArray(array $arr)
    {
        return json_decode(json_encode($arr));
    }

    public function getCacheInbox()
    {
        return json_decode(Redis::get('gmail.inbox.list'));
    }

    public function setCacheInbox($data)
    {
        return Redis::set('gmail.inbox.list', json_encode($data));
    }
}
