<?php

namespace App\Console\Commands;

use App\Models\ApiKey;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateApiKey extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:key:generate {name} {--expires= : Expiry date for the API key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a new API key for the banking API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');
        $expires = $this->option('expires');

        $apiKey = ApiKey::create([
            'name' => $name,
            'key' => Str::random(64),
            'is_active' => true,
            'expires_at' => $expires,
        ]);

        $this->info("API key generated successfully!");
        $this->info("Key: {$apiKey->key}");
        $this->info("Name: {$apiKey->name}");
        $this->info("Expires: " . ($apiKey->expires_at ? $apiKey->expires_at->format('Y-m-d H:i:s') : 'Never'));

        return Command::SUCCESS;
    }
}