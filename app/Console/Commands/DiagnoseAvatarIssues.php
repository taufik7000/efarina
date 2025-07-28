<?php

// app/Console/Commands/DiagnoseAvatarIssues.php
// Enhanced version dengan lebih detail

namespace App\Console\Commands;

use App\Models\User;
use App\Models\EmployeeProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DiagnoseAvatarIssues extends Command
{
    protected $signature = 'debug:avatar-issues {--user-id= : Specific user ID to check} {--fix : Attempt to fix common issues}';
    protected $description = 'Diagnose and optionally fix avatar display issues in Filament';

    public function handle()
    {
        $this->info('🔍 Enhanced Avatar Issues Diagnosis...');
        $this->newLine();

        // Check storage configuration
        $this->checkStorageConfiguration();
        
        // Check specific user or all users
        if ($userId = $this->option('user-id')) {
            $this->checkSpecificUser($userId);
        } else {
            $this->checkAllUsers();
        }

        // Offer to fix issues
        if ($this->option('fix')) {
            $this->fixCommonIssues();
        }

        $this->newLine();
        $this->info('✅ Enhanced diagnosis complete!');
    }

    private function checkStorageConfiguration()
    {
        $this->info('📁 Checking Storage Configuration...');
        
        // Check if public disk is configured
        $publicDisk = Storage::disk('public');
        $this->line("Public disk path: " . $publicDisk->path(''));
        
        // Check if storage link exists
        $storagePath = public_path('storage');
        if (is_link($storagePath)) {
            $this->line("✅ Storage link exists: {$storagePath}");
            $linkTarget = readlink($storagePath);
            $this->line("  → Points to: {$linkTarget}");
        } else {
            $this->error("❌ Storage link missing! Run: php artisan storage:link");
        }
        
        // Check profile-photos directory
        $profilePhotosPath = 'profile-photos';
        if ($publicDisk->exists($profilePhotosPath)) {
            $this->line("✅ Profile photos directory exists");
            $files = $publicDisk->files($profilePhotosPath);
            $this->line("  → Contains " . count($files) . " files");
        } else {
            $this->warn("⚠️  Profile photos directory doesn't exist, will be created on first upload");
        }
        
        // Check .env configuration
        $appUrl = config('app.url');
        $this->line("APP_URL: {$appUrl}");
        
        $this->newLine();
    }

    private function checkSpecificUser($userId)
    {
        $user = User::with('profile')->find($userId);
        
        if (!$user) {
            $this->error("❌ User with ID {$userId} not found!");
            return;
        }

        $this->info("👤 Detailed Check for User: {$user->name} (ID: {$user->id})");
        $this->checkUserAvatarDetailed($user);
    }

    private function checkAllUsers()
    {
        $this->info('👥 Checking All Users...');
        
        $users = User::with('profile')->get();
        $issueCount = 0;
        $totalUsers = $users->count();
        
        $this->info("Total users: {$totalUsers}");
        $this->newLine();

        foreach ($users as $user) {
            $hasIssue = $this->checkUserAvatarDetailed($user, false);
            if ($hasIssue) {
                $issueCount++;
            }
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("- Users with avatar issues: {$issueCount}");
        $this->line("- Users without issues: " . ($totalUsers - $issueCount));
        
        // Show users with issues
        if ($issueCount > 0) {
            $this->newLine();
            $this->warn("Users with avatar issues:");
            $usersWithIssues = User::with('profile')->get()->filter(function ($user) {
                return $this->hasAvatarIssue($user);
            });
            
            foreach ($usersWithIssues as $user) {
                $this->line("  • {$user->name} (ID: {$user->id})");
            }
        }
    }

    private function checkUserAvatarDetailed(User $user, bool $verbose = true): bool
    {
        $hasIssue = false;
        
        if ($verbose) {
            $this->newLine();
            $this->line("═══════════════════════════════════");
            $this->line("Checking: {$user->name} (ID: {$user->id})");
            $this->line("Email: {$user->email}");
        }

        // Check if profile exists
        $profile = $user->profile;
        if (!$profile) {
            if ($verbose) $this->error("❌ No EmployeeProfile found");
            $hasIssue = true;
            
            // Try to create profile if verbose mode
            if ($verbose) {
                $this->warn("  → Attempting to create profile...");
                try {
                    $profile = $user->getOrCreateProfile();
                    $this->info("  ✅ Profile created successfully");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed to create profile: " . $e->getMessage());
                }
            }
        } else {
            if ($verbose) $this->line("✅ EmployeeProfile exists (ID: {$profile->id})");
            
            // Check profile_photo_path
            $photoPath = $profile->profile_photo_path;
            if (!$photoPath) {
                if ($verbose) $this->warn("⚠️  No profile_photo_path set in database");
            } else {
                if ($verbose) $this->line("📄 Photo path in DB: {$photoPath}");
                
                // Check if file exists on disk
                if (Storage::disk('public')->exists($photoPath)) {
                    if ($verbose) {
                        $this->line("✅ Photo file exists on disk");
                        
                        // Check file details
                        $fileSize = Storage::disk('public')->size($photoPath);
                        $this->line("📏 File size: " . number_format($fileSize / 1024, 2) . " KB");
                        
                        $mimeType = Storage::disk('public')->mimeType($photoPath);
                        $this->line("🎭 MIME type: {$mimeType}");
                        
                        // Test URL generation
                        $url = Storage::disk('public')->url($photoPath);
                        $this->line("🔗 Generated URL: {$url}");
                        
                        // Test accessibility
                        $fullPath = Storage::disk('public')->path($photoPath);
                        $isReadable = is_readable($fullPath);
                        $this->line("👁️  File readable: " . ($isReadable ? 'Yes' : 'No'));
                    }
                    
                    // Test getFilamentAvatarUrl method
                    try {
                        $avatarUrl = $user->getFilamentAvatarUrl();
                        if ($avatarUrl) {
                            if ($verbose) $this->line("✅ getFilamentAvatarUrl() works: {$avatarUrl}");
                        } else {
                            if ($verbose) $this->error("❌ getFilamentAvatarUrl() returns null");
                            $hasIssue = true;
                        }
                    } catch (\Exception $e) {
                        if ($verbose) $this->error("❌ getFilamentAvatarUrl() throws error: " . $e->getMessage());
                        $hasIssue = true;
                    }
                } else {
                    if ($verbose) $this->error("❌ Photo file does not exist on disk");
                    if ($verbose) $this->line("  Expected path: " . Storage::disk('public')->path($photoPath));
                    $hasIssue = true;
                }
            }
        }

        if (!$verbose && $hasIssue) {
            $this->line("❌ {$user->name} (ID: {$user->id}) has avatar issues");
        }

        return $hasIssue;
    }

    private function hasAvatarIssue(User $user): bool
    {
        $profile = $user->profile;
        
        if (!$profile) {
            return true;
        }

        $photoPath = $profile->profile_photo_path;
        if (!$photoPath || !Storage::disk('public')->exists($photoPath)) {
            return false; // No photo is not necessarily an issue
        }

        try {
            $avatarUrl = $user->getFilamentAvatarUrl();
            return $avatarUrl === null;
        } catch (\Exception $e) {
            return true;
        }
    }

    private function fixCommonIssues()
    {
        $this->newLine();
        $this->info('🔧 Attempting to fix common issues...');
        
        // Fix 1: Create missing profiles
        $usersWithoutProfiles = User::whereDoesntHave('profile')->get();
        if ($usersWithoutProfiles->count() > 0) {
            $this->line("Creating missing profiles for {$usersWithoutProfiles->count()} users...");
            foreach ($usersWithoutProfiles as $user) {
                try {
                    $user->getOrCreateProfile();
                    $this->line("  ✅ Created profile for {$user->name}");
                } catch (\Exception $e) {
                    $this->error("  ❌ Failed to create profile for {$user->name}: " . $e->getMessage());
                }
            }
        }

        // Fix 2: Clean up invalid photo paths
        $profilesWithInvalidPaths = EmployeeProfile::whereNotNull('profile_photo_path')->get()
            ->filter(function ($profile) {
                return !Storage::disk('public')->exists($profile->profile_photo_path);
            });

        if ($profilesWithInvalidPaths->count() > 0) {
            $this->line("Cleaning up {$profilesWithInvalidPaths->count()} invalid photo paths...");
            foreach ($profilesWithInvalidPaths as $profile) {
                $oldPath = $profile->profile_photo_path;
                $profile->update(['profile_photo_path' => null]);
                $this->line("  ✅ Cleared invalid path for {$profile->user->name}: {$oldPath}");
            }
        }

        $this->info('🔧 Fix attempts completed!');
    }
}