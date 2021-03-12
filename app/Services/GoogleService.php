<?php

namespace App\Services;

class GoogleService
{
    private $gmail;
    private $calendar;

    public function __get($name)
    {
        return $this->{$name};
    }

    public function __set($name, $value)
    {
        $this->{$name} = $value;
    }
}
