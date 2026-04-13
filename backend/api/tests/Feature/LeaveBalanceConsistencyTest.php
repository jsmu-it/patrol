<?php

namespace Tests\Feature;

use App\Models\LeaveType;
use App\Models\User;
use App\Models\LeaveBalance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaveBalanceConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_resource_returns_all_active_leave_types_even_with_no_balances(): void
    {
        // Create some active leave types
        LeaveType::create(['name' => 'Cuti Tahunan', 'is_active' => true, 'default_quota' => 12]);
        LeaveType::create(['name' => 'Cuti Melahirkan', 'is_active' => true, 'default_quota' => 90]);
        // Create an inactive leave type
        LeaveType::create(['name' => 'Inactive Type', 'is_active' => false, 'default_quota' => 0]);

        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertStatus(200);
        
        // Laravel wraps JsonResource in 'data' by default when returned from controller
        $leaveBalances = $response->json('data.leave_balances');
        
        $this->assertCount(2, $leaveBalances);
        
        // Assertions for Cuti Tahunan (no record in DB, should use defaults)
        $tahunan = collect($leaveBalances)->firstWhere('leave_type_name', 'Cuti Tahunan');
        $this->assertNotNull($tahunan);
        $this->assertEquals(12, $tahunan['quota']);
        $this->assertEquals(0, $tahunan['used']);
        $this->assertEquals(12, $tahunan['remaining']);

        // Assertions for Inactive Type (should NOT be included if no balance)
        $inactive = collect($leaveBalances)->firstWhere('leave_type_name', 'Inactive Type');
        $this->assertNull($inactive);
    }

    public function test_user_resource_merges_active_types_with_existing_balances(): void
    {
        $tahunan = LeaveType::create(['name' => 'Cuti Tahunan', 'is_active' => true, 'default_quota' => 12]);
        $melahirkan = LeaveType::create(['name' => 'Cuti Melahirkan', 'is_active' => true, 'default_quota' => 90]);
        
        $user = User::factory()->create();
        
        // Create an existing balance record for Cuti Tahunan
        LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $tahunan->id,
            'year' => now()->year,
            'quota' => 15, // Overridden quota
            'used' => 3,
        ]);

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertStatus(200);
        $leaveBalances = $response->json('data.leave_balances');
        
        $this->assertCount(2, $leaveBalances);
        
        // Check overridden balance
        $tahunanData = collect($leaveBalances)->firstWhere('leave_type_id', $tahunan->id);
        $this->assertEquals(15, $tahunanData['quota']);
        $this->assertEquals(3, $tahunanData['used']);
        $this->assertEquals(12, $tahunanData['remaining']);
        
        // Check default balance for Melahirkan
        $melahirkanData = collect($leaveBalances)->firstWhere('leave_type_id', $melahirkan->id);
        $this->assertEquals(90, $melahirkanData['quota']);
        $this->assertEquals(0, $melahirkanData['used']);
    }

    public function test_user_resource_includes_inactive_type_if_balance_exists(): void
    {
        $inactive = LeaveType::create(['name' => 'Old Type', 'is_active' => false, 'default_quota' => 0]);
        $user = User::factory()->create();
        
        LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $inactive->id,
            'year' => now()->year,
            'quota' => 5,
            'used' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/me');

        $response->assertStatus(200);
        $leaveBalances = $response->json('data.leave_balances');
        
        $inactiveData = collect($leaveBalances)->firstWhere('leave_type_id', $inactive->id);
        $this->assertNotNull($inactiveData);
        $this->assertEquals(5, $inactiveData['quota']);
        $this->assertEquals(1, $inactiveData['used']);
    }
}
