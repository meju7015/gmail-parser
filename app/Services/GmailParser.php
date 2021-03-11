<?php

namespace App\Services;

use Carbon\Carbon;
use phpDocumentor\Reflection\Types\Object_;

class GmailParser
{
    private $origin;
    private $title = '';
    private $jobs = [];
    private $user = [];
    private $data = [];
    private $cc = [];
    private $bcc = [];
    private $date;

    private const GMAIL_HEADER_FROM = 'From';
    private const GMAIL_HEADER_TO = 'To';
    private const GMAIL_HEADER_REPLY_TO = 'Reply-To';
    private const GMAIL_HEADER_SUBJECT = 'Subject';
    private const GMAIL_HEADER_DATE = 'Date';
    private const GMAIL_HEADER_CC = 'Cc';
    private const GMAIL_HEADER_BCC = 'Bcc';

    public function __construct($origin)
    {
        $this->origin = $origin;
    }

    public function compactString($string, $spear = ','): array
    {
        return explode(
            $spear,
            trim(str_replace('"', "", $string))
        );
    }

    public function getHeaders(): array
    {
        return [
            'user' => $this->user,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'date' => $this->date,
            'title' => $this->title
        ];
    }

    public function getOrigin(): \Google_Service_Gmail
    {
        return $this->origin;
    }

    public function parseThreadToMessageBody($id): array
    {
        $messages = $this->origin->getThread($id);
        foreach ($messages as $msg) {
            $body = $msg->getPayload()->getBody();
            $parts = $msg->getPayload()->getParts();
            array_push($this->data, base64_decode($body->getData()));
            foreach ($parts as $part) {
                array_push($this->data, base64_decode($part->getBody()->getData()));
            }
        }

        return $this->data;
    }

    public function parseThreadToMessageHeader($id): array
    {
        $messages = $this->origin->getThread($id);
        foreach ($messages as $message) {
            $headers = $message->getPayload()->getHeaders();
            foreach ($headers as $header) {
                switch ($header->name) {
                    case self::GMAIL_HEADER_TO:
                        $this->user = array_merge(
                            $this->user,
                            $this->compactString($header->value)
                        );
                        break;
                    case self::GMAIL_HEADER_CC:
                        $this->cc = array_merge(
                            $this->cc,
                            $this->compactString($header->value)
                        );
                        break;
                    case self::GMAIL_HEADER_BCC:
                        $this->bcc = array_merge(
                            $this->bcc,
                            $this->compactString($header->value)
                        );
                        break;
                    case self::GMAIL_HEADER_SUBJECT:
                        $this->title = $header->value;
                        break;
                    case self::GMAIL_HEADER_DATE:
                        $this->date = Carbon::make($header->value)->format('Y-m-d H:i:s');
                }
            }
        }

        return $this->getHeaders();
    }
}
