<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Api\AssessmentApiService;
use App\Http\Resources\V1\AssessmentDetailResource;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AssessmentController extends Controller
{
    use ApiResponse;

    protected $assessmentService;

    public function __construct(AssessmentApiService $assessmentService)
    {
        $this->assessmentService = $assessmentService;
    }

    /**
     * Get Assessment Details.
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            // 1. Fetch Data via Service
            $assessment = $this->assessmentService->getAssessmentDetails($id);

            // 2. Authorization Check (Policy)
            // Note: Gate::authorize needs the policy registered in AuthServiceProvider 
            // or implicitly discovered if following conventions.
            // Using explicit check for clarity and error handling:
            if (Gate::denies('view', $assessment)) {
                throw new AccessDeniedHttpException('This action is unauthorized.');
            }

            // 3. Transform Data (Resource)
            $data = new AssessmentDetailResource($assessment);

            // 4. Return Standardized Response
            return $this->successResponse('Assessment details retrieved successfully', $data);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Assessment not found', null, 404);
        } catch (AccessDeniedHttpException $e) {
            return $this->errorResponse($e->getMessage(), null, 403);
        } catch (\Exception $e) {
            // Log error in production
            return $this->errorResponse('Internal Server Error: ' . $e->getMessage(), null, 500);
        }
    }
}
