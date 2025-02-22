<?php

namespace App;

class Customer {
    public string $id;
    public string $name;
    public string $dateOfBirth;
    public array $faxes = [];

    public function __construct(string $id, string $name, string $dateOfBirth) {
        $this->id = $id;
        $this->name = $name;
        $this->dateOfBirth = $dateOfBirth;
    }
}
