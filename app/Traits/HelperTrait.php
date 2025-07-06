<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait HelperTrait
{
    /**
     * Given an enum, returns an associative array with three keys:
     * - 'value': the value of the enum
     * - 'slug': the name of the enum
     * - 'label': the label of the enum, or its name if no label is set
     *
     * @param \BackedEnum $enum
     * @return array
     */
    public function getEnumInfo($enum): array
    {
        return [
            'value' => $enum->value,
            'slug'  => $enum->name,
            'label' => $enum->label ?? $enum->name,
        ];
    }

    public function apiResponse(string|null $msg = null, int $code = Response::HTTP_OK, $data = [], bool $error = false, array $errors = [], $key = null)
    {
        return response()->json([
            'key'    => $key ?? ($error ? 'fail' : 'success'),
            'msg'    => $msg ?? 'data retrieved successfully',
            'code'   => $code,
            'response_status' => [
                'error'             => $error,
                'validation_errors' => $errors
            ],
            'data'   => $this->checkIfEmpty($data) ? null : $data,
        ], $code);
    }
    protected function checkIfEmpty($data): bool
    {
        return $data instanceof \Illuminate\Http\Resources\Json\AnonymousResourceCollection ? $data->collection->isEmpty() : empty($data);
    }

    /**
     * Checks if the status of a model is allowed to transition to a new status.
     *
     * @param mixed $model The model to check.
     * @param int|string $newStatus The new status to transition to.
     * @param int|array $oldStatus The old status or statuses to check against.
     *
     * @return string|array If the transition is valid, returns an array with 'key' as 'success' and 'msg' as 'Status transition is valid.'. If the transition is invalid, returns an array with 'key' as 'fail' and 'msg' with the reason for the failure. If the model is not found, returns the string 'model not found'.
     */
    static function checkPreviousStatus($model, $newStatus, int|array $oldStatus): string|array
    {
        try {
            $currentStatus = $model->status->value;

            if ($currentStatus == $newStatus) {
                return ['key' => 'fail', 'msg' => 'This status is already set.'];
            }

            if (is_array($oldStatus) && !in_array($currentStatus, $oldStatus)) {
                return ['key' => 'fail', 'msg' => 'Current status is not allowed to transition to the new status.'];
            }

            if (is_numeric($oldStatus) && $currentStatus != $oldStatus) {
                return ['key' => 'fail', 'msg' => 'Invalid status transition.'];
            }

            return ['key' => 'success', 'msg' => 'Status transition is valid.'];
        } catch (\Exception $e) {
            return 'model not found';
        }
    }
}
