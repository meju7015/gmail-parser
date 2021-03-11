<?php

namespace App\Services;

use Google_Client;
use Google_Service_Gmail;

class GmailClient
{
    private $client;
    private $service;
    private $user;

    public function __construct($init = true, $user = 'me')
    {
        if ($init) {
            $this->setClient();
            $this->setService();
            $this->user = $user;
        }
    }

    public function setClient(Google_Client $client = null): void
    {
        if ($client === null) {
            $client = new Google_Client();
            $client->setApplicationName('Quickstart');
            $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
            $client->setAuthConfig(base_path() . '/credentials.json');
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            $tokenPath = base_path() . '/token.json';
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

    public function setService(\Google_Service $service = null): void
    {
        if ($service === null) {
            $service = new Google_Service_Gmail($this->client);
        }

        $this->service = $service;
    }

    public function getThreads()
    {
        return $this->service->users_threads->listUsersThreads($this->user);
    }

    public function getMessages()
    {
        return $this->service->users_messages->listUsersMessages($this->user);
    }

    public function getThread(string $id)
    {
        return $this->service->users_threads->get($this->user, $id);
    }

    public function getMessage(string $id)
    {
        return $this->service->users_messages->get($this->user, $id);
    }
}
