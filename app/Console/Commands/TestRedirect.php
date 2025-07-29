<?php

namespace App\Console\Commands;

use App\Models\Redirect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestRedirect extends Command
{
    protected $signature = 'redirect:test {old_url}';
    protected $description = 'Test redirect functionality';

    public function handle()
    {
        $oldUrl = $this->argument('old_url');
        $cleanPath = ltrim($oldUrl, '/');
        
        $this->info("Testing redirect for: $cleanPath");
        
        // Cari di database
        $redirect = Redirect::active()
            ->where('old_url', $cleanPath)
            ->first();
            
        if (!$redirect) {
            $this->error("No active redirect found for: $cleanPath");
            
            // Tampilkan semua redirect yang ada
            $allRedirects = Redirect::all();
            $this->info("Available redirects:");
            foreach ($allRedirects as $r) {
                $this->line("- {$r->old_url} -> {$r->new_url} (active: " . ($r->is_active ? 'yes' : 'no') . ")");
            }
            return;
        }
        
        $this->info("Found redirect:");
        $this->line("- From: {$redirect->old_url}");
        $this->line("- To: {$redirect->new_url}");
        $this->line("- Status: {$redirect->status_code}");
        $this->line("- Active: " . ($redirect->is_active ? 'yes' : 'no'));
        
        // Test HTTP request
        $testUrl = url($oldUrl);
        $this->info("Testing HTTP request to: $testUrl");
        
        try {
            $response = Http::withOptions(['allow_redirects' => false])
                ->get($testUrl);
                
            $this->info("Response status: " . $response->status());
            
            if ($response->redirect()) {
                $this->info("Redirect location: " . $response->header('location'));
            } else {
                $this->warn("No redirect header found");
            }
            
        } catch (\Exception $e) {
            $this->error("HTTP test failed: " . $e->getMessage());
        }
    }
}