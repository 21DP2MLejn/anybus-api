<?php

namespace App\Http\Requests;

use App\Enums\JobAction;
use Illuminate\Foundation\Http\FormRequest;

class JobTransitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [];

        // Get the action from the route
        $action = $this->route('action') ?? $this->input('action');

        if ($action) {
            $jobAction = JobAction::from($action);

            // Add comment validation if required
            if ($jobAction->requiresComment()) {
                $rules['comment'] = [
                    'required',
                    'string',
                    'min:'.$jobAction->getMinCommentLength(),
                    'max:1000',
                ];
            }

            // Metadata is optional but must be an array if provided
            $rules['metadata'] = 'sometimes|array';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $action = $this->route('action') ?? $this->input('action');
        $minLength = 30; // default

        if ($action) {
            try {
                $jobAction = JobAction::from($action);
                $minLength = $jobAction->getMinCommentLength();
            } catch (\Exception $e) {
                // Use default
            }
        }

        return [
            'comment.required' => 'A comment is required for this action.',
            'comment.min' => "Comment must be at least {$minLength} characters long.",
            'comment.max' => 'Comment must not exceed 1000 characters.',
            'metadata.array' => 'Metadata must be a valid array.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'comment' => 'transition comment',
            'metadata' => 'additional metadata',
        ];
    }
}
