<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Console\Command;

class InitializeLeaveBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:initialize-balances {--year= : Year to initialize (default: current year)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize leave balances for all users who do not have them yet';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $year = $this->option('year') ?? now()->year;
        
        $this->info("Initializing leave balances for year {$year}...");
        
        $activeLeaveTypes = LeaveType::active()->get();
        
        if ($activeLeaveTypes->isEmpty()) {
            $this->error('No active leave types found. Please create leave types first.');
            return Command::FAILURE;
        }
        
        $this->info("Found {$activeLeaveTypes->count()} active leave types.");
        
        $users = User::all();
        $usersProcessed = 0;
        $balancesCreated = 0;
        
        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();
        
        foreach ($users as $user) {
            $userHadBalances = false;
            
            foreach ($activeLeaveTypes as $leaveType) {
                // Check if balance already exists
                $existingBalance = LeaveBalance::where('user_id', $user->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year)
                    ->first();
                
                if (!$existingBalance) {
                    LeaveBalance::create([
                        'user_id' => $user->id,
                        'leave_type_id' => $leaveType->id,
                        'quota' => $leaveType->default_quota ?? 0,
                        'used' => 0,
                        'year' => $year,
                    ]);
                    
                    $balancesCreated++;
                    $userHadBalances = true;
                }
            }
            
            if ($userHadBalances) {
                $usersProcessed++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("✓ Processed {$users->count()} users");
        $this->info("✓ Created {$balancesCreated} leave balance records");
        $this->info("✓ {$usersProcessed} users received new leave balances");
        
        return Command::SUCCESS;
    }
}
