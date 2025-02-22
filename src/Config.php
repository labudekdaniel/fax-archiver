<?php

namespace App;

class Config {
    private array $settings;

    public function __construct(string $configFile) {
        if (!file_exists($configFile)) {
            throw new \Exception("Configuration file not found: $configFile");
        }

        $configData = file_get_contents($configFile);
        $this->settings = json_decode($configData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error parsing JSON: " . json_last_error_msg());
        }
    }

    public function get(string $path, $default = null) {
        $keys = explode('.', $path);
        $value = $this->settings;

        foreach ($keys as $key) {
            if (!isset($value[$key])) {
                return $default;
            }
            $value = $value[$key];
        }

        return $value;
    }
}
