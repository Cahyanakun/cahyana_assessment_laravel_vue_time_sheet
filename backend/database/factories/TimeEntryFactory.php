<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'employee_id' => Employee::factory(),
            'project_id' => Project::factory(),
            'task_id' => Task::factory(),
            'date' => $this->faker->date(),
            'hours' => $this->faker->randomFloat(2, 0.5, 8),
        ];
    }
}
