<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetGuardPasswords extends Command
{
    protected $signature = 'guards:reset-passwords {password=guard2025}';
    protected $description = 'Reset all guard user passwords to a default value';

    public function handle()
    {
        $newPassword = $this->argument('password');
        
        $this->info("Resetting passwords for all GUARD users to: {$newPassword}");
        
        // Find all users with GUARD role
        $guards = User::where('role', User::ROLE_GUARD)->get();
        
        $count = $guards->count();
        
        if ($count === 0) {
            $this->warn('No GUARD users found.');
            return 0;
        }
        
        $this->info("Found {$count} GUARD users.");
        
        if (!$this->confirm('Do you want to proceed with password reset?')) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        foreach ($guards as $guard) {
            $guard->password = Hash::make($newPassword);
            $guard->save();
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        
        $this->info("Successfully reset passwords for {$count} GUARD users.");
        
        return 0;
    }
}
