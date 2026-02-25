<?php

namespace Database\Seeders;

use App\Models\PricingPlan;
use Illuminate\Database\Seeder;

class PricingPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $plans = [
            [
                'name' => 'Free',
                'description' => 'Basic plan with limited features',
                'price' => 0,
                'role' => 'user',
                'can_create_tenant' => false,
                'can_edit_tenant' => false,
                'can_delete_tenant' => false,
                'can_create_tasks' => false,
                'can_create_projects' => false,
                'can_edit_tasks' => false,
                'can_edit_projects' => false,
                'can_delete_tasks' => false,
                'can_delete_projects' => false,
                'can_invite_members' => false,
            ],

            [
                'name' => 'Pro',
                'description' => 'Professional plan with advanced features',
                'price' => 30,
                'role' => ['admin', 'user', 'tenant_admin', 'project_admin'],
                'can_create_tenant' => true,
                'can_edit_tenant' => true,
                'can_delete_tenant' => true,
                'can_create_tasks' => true,
                'can_create_projects' => true,
                'can_edit_tasks' => true,
                'can_edit_projects' => true,
                'can_delete_tasks' => true,
                'can_delete_projects' => true,
                'can_invite_members' => true,
            ],
        ];

        foreach ($plans as $plan) {
            PricingPlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
