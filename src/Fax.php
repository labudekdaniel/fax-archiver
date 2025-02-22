<?php

namespace App;

class Fax {
    public string $timestamp;
    public string $path;

    public function __construct(string $timestamp, string $path) {
        $this->timestamp = $timestamp;
        $this->path = $path;
    }
}
