<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use App\Services\GmailClient;

class GmailClientController extends Controller
{
    function getGmailClient() {
        $client = (new GmailClient())->getClient();
    }
}
