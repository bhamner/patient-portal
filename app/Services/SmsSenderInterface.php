<?php

namespace App\Services;

interface SmsSenderInterface
{
    public function send(string $to, string $message): void;
}

