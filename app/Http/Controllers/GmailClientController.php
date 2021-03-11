<?php

namespace App\Http\Controllers;

use App\Services\GmailClient;
use App\Services\GmailParser;

class GmailClientController extends Controller
{
    function getGmailClient() {

        $parser = new GmailParser(
            new GmailClient()
        );

        dump($parser->parseThreadToMessageHeader(
            '1781f18728ba3dc9'
        ));



        /*$data = [];

        foreach ($gmail->getThreads()->getThreads() as $key => $item) {
            $messages = $gmail->getThread($item->id);
            foreach ($messages as $msg) {
                $body = $msg->getPayload()->getBody();
                $parts = $msg->getPayload()->getParts();

                array_push($data, $body->getData());

                foreach ($parts as $part) {
                    array_push($data, $part->getBody()->getData());
                }
            }

            if ($key > 10) break;
        }

        foreach ($data as $d) {
            dump(base64_decode($d));
        }*/
    }
}
