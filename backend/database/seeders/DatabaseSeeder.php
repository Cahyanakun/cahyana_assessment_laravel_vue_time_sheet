<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial Cleanup
        Schema::disableForeignKeyConstraints();
        TimeEntry::truncate();
        Task::truncate();
        Project::truncate();
        DB::table('company_employee')->truncate();
        Employee::truncate();
        Company::truncate();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // 2. Create Companies
        $companies = Company::factory(3)->create();

        // 3. Create Employees
        $employees = Employee::factory(5)->create();

        // 4. Hierarchical Setup for each Company
        foreach ($companies as $company) {
            // Assign some employees to this company
            $company->employees()->attach(
                $employees->random(rand(2, 4))->pluck('id')->toArray()
            );

            // Create 2 Projects per Company
            Project::factory(2)->create(['company_id' => $company->id]);

            // Create 3 Tasks per Company
            Task::factory(3)->create(['company_id' => $company->id]);
        }

        // 5. Edge Case Testing Scenario
        $firstEmployee = $employees->first();
        $firstCompany = $companies->first();
        
        // Ensure the first employee is attached to the first company
        $firstCompany->employees()->syncWithoutDetaching([$firstEmployee->id]);

        $firstProject = $firstCompany->projects->first();
        $firstTask = $firstCompany->tasks->first();

        TimeEntry::create([
            'company_id' => $firstCompany->id,
            'employee_id' => $firstEmployee->id,
            'project_id' => $firstProject->id,
            'task_id' => $firstTask->id,
            'date' => '2026-05-10',
            'hours' => 4.0,
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info("Edge Case: Employee ID {$firstEmployee->id} has entry for Project ID {$firstProject->id} on 2026-05-10.");
    }
}
