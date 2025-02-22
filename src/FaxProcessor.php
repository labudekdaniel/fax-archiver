<?php

namespace App;

class FaxProcessor {
    private string $faxDir;
    private string $processedFaxesPath;
    private array $customers = [];
    private array $processedFaxes = [];
    private Logger $logger;

    public function __construct(string $faxDir, string $peopleJsonPath, string $processedFaxesPath, Logger $logger) {
        $this->faxDir = $faxDir;
        $this->processedFaxesPath = $processedFaxesPath;
        $this->logger = $logger;
        $this->loadCustomers($peopleJsonPath);
        $this->loadProcessedFaxes();
    }

    private function loadProcessedFaxes(): void {
        if (file_exists($this->processedFaxesPath)) {
            $this->processedFaxes = json_decode(file_get_contents($this->processedFaxesPath), true);
        }
    }

    private function saveProcessedFaxes(): void {
        file_put_contents($this->processedFaxesPath, json_encode($this->processedFaxes, JSON_PRETTY_PRINT));
    }

    private function loadCustomers(string $peopleJsonPath): void {
        $peopleJson = file_get_contents($peopleJsonPath);
        $peopleData = json_decode($peopleJson, true);

        foreach ($peopleData as $person) {
            $id = $person['id'] ?? null;
            $name = $person['name'] ?? '';
            $dateOfBirth = $person['date_of_birth'] ?? '';

            if (empty($id)) {
                $this->logger->log("Missing ID for customer data: " . json_encode($person), 'ERROR');
                continue;
            }

            if (empty($name)) {
                $this->logger->log("Missing name for ID: $id. Using ID as filename.", 'WARNING');
            }

            if (empty($dateOfBirth)) {
                $this->logger->log("Missing date of birth for ID: $id (Name: $name).", 'WARNING');
                $dateOfBirth = 'N/A';
            }

            $this->customers[$id] = new Customer($id, $name, $dateOfBirth);
        }
    }

    public function scanAndProcessFaxes(): array {
        foreach (scandir($this->faxDir) as $folder) {
            if ($folder === '.' || $folder === '..') continue;
            $folderPath = "{$this->faxDir}/{$folder}";
    
            $firstPage = "{$folderPath}/1.png";
            if (!file_exists($firstPage)) {
                $this->logger->log("Missing first page (1.png) in folder: $folder. Skipping fax.", 'ERROR');
                continue;
            }
    
            $qrOutput = shell_exec("zbarimg --raw " . escapeshellarg($firstPage));
            $uuid = trim($qrOutput);
    
            if (isset($this->customers[$uuid])) {
                $processedFolders = $this->processedFaxes[$uuid] ?? [];
    
                if (!in_array($folder, $processedFolders)) {
                    $this->customers[$uuid]->faxes[] = new Fax($folder, $folderPath);
                    $this->processedFaxes[$uuid][] = $folder;
                    $this->logger->log("Processed fax for ID: $uuid | Folder: $folder");
                }
            } else {
                $this->logger->log("Invalid or missing UUID in QR code for folder: $folder", 'ERROR');
            }
        }
    
        $this->saveProcessedFaxes();
        return $this->customers;
    }
    
}
