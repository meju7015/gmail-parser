<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;

class WebhookService
{
    const HTTP_HEADER_X_WWW_FORM = 'Content-Type: application/x-www-form-urlencoded';

    /**
     * 훗
     * @param $data
     * @return mixed
     */
    public function send($data)
    {
        $url = env('NATEON_WEBHOOK_URL', 'localhost');

        $msg = $data['job'] . "\n";
        $msg .= "일정 : " . $data['work_date'][0] . " ~ " . $data['work_date'][1] . "\n";
        $msg .= "이슈 : " . $data['issue'] . "\n";
        $msg .= "캘린더링크 : " . $data['link'];
        $msg = 'content=' . urlencode($msg);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [self::HTTP_HEADER_X_WWW_FORM]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $msg); // 메시지

        $result = curl_exec($ch);
        curl_close($ch);

    }
}
