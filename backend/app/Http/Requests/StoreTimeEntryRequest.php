<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimeEntryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization logic can be added later
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'entries' => ['required', 'array', 'min:1'],
            'entries.*.company_id' => ['required', 'exists:companies,id'],
            'entries.*.employee_id' => ['required', 'exists:employees,id'],
            'entries.*.project_id' => [
                'required',
                'exists:projects,id',
            ],
            'entries.*.task_id' => [
                'required',
                'exists:tasks,id',
                function ($attribute, $value, $fail) {
                    preg_match('/entries\.(\d+)\.task_id/', $attribute, $matches);
                    $index = $matches[1];
                    $companyId = $this->input("entries.{$index}.company_id");

                    if ($companyId && !\App\Models\Task::where('id', $value)->where('company_id', $companyId)->exists()) {
                        $fail('The selected task does not belong to the chosen company.');
                    }
                },
            ],
            'entries.*.date' => ['required', 'date', 'date_format:Y-m-d'],
            'entries.*.hours' => ['required', 'numeric', 'min:0.01', 'max:24'],
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'data' => null,
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
