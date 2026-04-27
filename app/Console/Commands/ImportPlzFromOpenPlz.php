<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ImportPlzFromOpenPlz extends Command
{
    protected $signature   = 'plz:import-openplz';
    protected $description = 'Import German PLZ data from openplzapi.org (only real cities, no companies)';

    // German PLZ range: 01001 – 99998
    // We query page by page using 2-digit prefixes 01..99
    public function handle(): int
    {
        $this->info('Truncating postal_codes table...');
        DB::table('postal_codes')->truncate();

        $inserted = 0;
        $errors   = 0;
        $bar      = $this->output->createProgressBar(99);
        $bar->start();

        for ($prefix = 1; $prefix <= 99; $prefix++) {
            $prefixStr = str_pad($prefix, 2, '0', STR_PAD_LEFT);
            $page      = 1;

            do {
                try {
                    $resp = Http::timeout(30)->get('https://openplzapi.org/de/Localities', [
                        'postalCode' => $prefixStr,
                        'page'       => $page,
                        'pageSize'   => 50,
                    ]);

                    if (!$resp->successful()) {
                        $errors++;
                        break;
                    }

                    $items = $resp->json();
                    if (empty($items)) {
                        break;
                    }

                    $rows = [];
                    $now  = now();
                    foreach ($items as $item) {
                        $rows[] = [
                            'plz'        => $item['postalCode'],
                            'ort'        => $item['name'],
                            'bundesland' => $item['federalState']['name'] ?? '',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                        $inserted++;
                    }

                    DB::table('postal_codes')->insert($rows);

                    // Check if there might be more pages
                    $hasMore = count($items) === 50;
                    $page++;

                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("Error for prefix {$prefixStr} page {$page}: " . $e->getMessage());
                    $errors++;
                    break;
                }

                // Small pause to be polite to the API
                usleep(50000); // 50ms

            } while ($hasMore);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Done! Inserted: {$inserted} records. Errors: {$errors}");

        return self::SUCCESS;
    }
}
