<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Object_;

class GmailParser
{
    private $thread;

    private $messages = [];

    private $title = '';

    private $job = '';

    private $issue = '';

    private $user = [];

    private $data = [];

    private $workDate = [];

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

    private const GMAIL_BODY_DATE = '일정 :*';

    private const GMAIL_BODY_TITLE = '작업 :*';

    private const GMAIL_BODY_ISSUE = '이슈 :*';

    public function __construct($origin, $id)
    {
        $this->thread = $origin->getThread($id);
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

    public function parseThreadToMessageBody(): array
    {
        $messages = $this->thread;
        foreach ($messages as $msg) {

            $this->messages['id'] = $msg->id;
            $this->messages['labellds'] = $msg->labellds;
            $this->messages['snippets'] = $msg->snippets;

            $parts = $msg->getPayload()->getParts();

            $stringLine = $this->getLineOfString(
                $this->getPayloads($parts)
            );

            foreach ($stringLine as $key => $item) {
                if (Str::of($item)->is(self::GMAIL_BODY_DATE)) {
                    $this->workDate = explode('~', $this->replace($item, self::GMAIL_BODY_DATE));
                }

                if (Str::of($item)->is(self::GMAIL_BODY_TITLE)) {
                    $this->job = trim($this->replace($item, self::GMAIL_BODY_TITLE));
                }

                if (Str::of($item)->is(self::GMAIL_BODY_ISSUE)) {
                    $this->issue = trim($this->replace($item, self::GMAIL_BODY_ISSUE));
                }
            }
        }

        return [
            'work_date' => $this->workDate,
            'job' => $this->job,
            'issue' => $this->issue
        ];
    }

    public function replace($string, $type)
    {
        return str_replace(
            str_replace('*', '', $type),
            '',
            Str::of($string)->after($type)
        );
    }

    public function getLineOfString($string)
    {
        if (gettype($string) === 'array') {
            $toString = [];
            foreach ($string as $item) {
                $toString = array_merge($toString, explode("\r\n", trim((string)$item)));
            }
            return $toString;
        } else {
            return explode("\r\n", trim((string)$string));
        }
    }

    public function getPayloads($payload): array
    {
        if (gettype($payload) === 'array') {
            foreach ($payload as $item) {
                $part = $item->getParts();
                if ($item->getBody()->data !== null) {
                    array_push(
                        $this->data,
                        $this->decode($item->getBody()->data)
                    );
                }
                if (method_exists($item, 'getParts')) {
                    $this->getPayloads($part);
                }
            }
        }
        return $this->data;
    }

    private function decode($string)
    {
        return base64_decode(strtr($string, ['-' => '+', '_' => '/']));
    }

    private function trim($string)
    {
        return preg_replace("/\s+/", "", $string);
    }

    public function parseThreadToMessageHeader(): array
    {
        $messages = $this->thread;
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

    public function parseMessageBodyToFormat(): array
    {
        if (!$this->data) {
            $this->parseThreadToMessageBody();
        }

        return [
            'work_date' => $this->workDate,
            'job' => $this->job,
            'issue' => $this->issue
        ];
    }
}
