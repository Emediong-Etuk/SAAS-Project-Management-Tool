<?php

namespace App\Contracts\Interface;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface AWSChimeInterface
{
    //
    public function createMeeting(Request $request, Project $project): JsonResponse;

    public function createAttendee(Project $project, Request $request): JsonResponse;

    public function getMeeting(Project $project): JsonResponse;

    public function getAttendee(Project $project, Request $request): JsonResponse;

    public function listAttendees(Project $project): JsonResponse;

    public function deleteMeeting(Project $project): JsonResponse;

    public function deleteAttendee(Project $project, Request $request): JsonResponse;
}
