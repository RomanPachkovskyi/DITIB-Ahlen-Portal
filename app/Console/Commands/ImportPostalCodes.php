<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\PostalCode;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

#[Signature('app:import-postal-codes')]
#[Description('Downloads and imports German postal codes into the database')]
class ImportPostalCodes extends Command
{
    public function handle()
    {
        $this->info('Downloading DE.zip from GeoNames...');
        
        $url = 'https://download.geonames.org/export/zip/DE.zip';
        $zipContent = Http::get($url)->body();
        
        $zipPath = storage_path('app/DE.zip');
        file_put_contents($zipPath, $zipContent);
        
        $this->info('Extracting DE.txt...');
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo(storage_path('app/'));
            $zip->close();
        } else {
            $this->error('Failed to extract ZIP.');
            return;
        }

        $txtPath = storage_path('app/DE.txt');
        if (!file_exists($txtPath)) {
            $this->error('DE.txt not found.');
            return;
        }

        $this->info('Parsing data and importing (this might take a minute)...');
        
        $handle = fopen($txtPath, 'r');
        
        PostalCode::truncate();
        
        $count = 0;
        $batch = [];
        
        while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
            // column 1 is plz, 2 is ort, 3 is bundesland (admin name1)
            if (isset($data[1]) && isset($data[2]) && isset($data[3])) {
                $batch[] = [
                    'plz' => $data[1],
                    'ort' => $data[2],
                    'bundesland' => $data[3],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                if (count($batch) >= 1000) {
                    PostalCode::insert($batch);
                    $count += count($batch);
                    $batch = [];
                }
            }
        }
        
        if (count($batch) > 0) {
            PostalCode::insert($batch);
            $count += count($batch);
        }
        
        fclose($handle);
        unlink($zipPath);
        unlink($txtPath);
        
        $this->info("Successfully imported $count postal codes.");
    }
}
