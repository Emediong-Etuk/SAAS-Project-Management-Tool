<?php

namespace App\ThirdParty;

use App\Contracts\Interface\AWSChimeInterface;
use App\Enum\MeetingStatus;
use App\Models\Project;
use App\Support\Repositories\ProjectRepository;
use App\Support\Repositories\UserRepository;
use App\Traits\GenerateClientRequestToken;
use App\Traits\GenerateMeetingId;
use Aws\ChimeSDKMeetings\ChimeSDKMeetingsClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AWSChimeApi extends BaseThirdParty implements AWSChimeInterface
{
    /**
     * Create a new class instance.
     */
    use GenerateClientRequestToken, GenerateMeetingId;

    protected $chime;

    private ProjectRepository $projectRepository;

    private UserRepository $userRepository;

    public function __construct(ChimeSDKMeetingsClient $chime)
    {
        //
        $this->chime = $chime;
        $this->projectRepository = app(ProjectRepository::class);
        $this->userRepository = app(UserRepository::class);
    }

    public function createMeeting(Request $request, Project $project): JsonResponse
    {
        $project_id = $project->id;
        $user_id = $request->user()->id;

        if ($project->meeting_status === MeetingStatus::TRUE->value) {
            return $this->badRequestResponse(message: 'Meeting already ongoing for this project.');
        }

        $meetingResponse = $this->chime->createMeeting([
            'ClientRequestToken' => $this->generateClientRequestToken(),
            'ExternalMeetingId' => 'project-meeting-'.$this->generateMeetingId(),
            'MediaRegion' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'MeetingFeatures' => [
                'Attendee' => [
                    'MaxCount' => $this->projectRepository->getProjectMembersCount($project_id),
                ],
            ],
        ]);

        $meeting = $meetingResponse['Meeting'];

        $attendeeResponse = $this->chime->createAttendee([
            'MeetingId' => $meeting['MeetingId'],
            'ExternalUserId' => $user_id,
        ]);

        $attendee = $attendeeResponse['Attendee'];

        $this->projectRepository->update($project_id, ['meeting_status' => MeetingStatus::TRUE->value, 'meeting_id' => trim($meeting['MeetingId'])]);
        $this->userRepository->update($request->user()->id, ['attendee_id' => $attendee['AttendeeId']]);

        return $this->successResponse(data: [
            'meeting' => $meeting,
            'attendee' => $attendee,
        ]);
    }

    public function createAttendee(Project $project, Request $request): JsonResponse
    {

        try {
            $attendeeResponse = $this->chime->createAttendee([
                'MeetingId' => $project->meeting_id,
                'ExternalUserId' => $request->user()->id,
            ]);

            $attendee = $attendeeResponse['Attendee'];
            $this->userRepository->update($request->user()->id, ['attendee_id' => $attendee['AttendeeId']]);

            return $this->successResponse(data: $attendee);
        } catch (\Exception $e) {
            return $this->badRequestResponse(message: $e->getMessage());
        }
    }

    public function getMeeting(Project $project): JsonResponse
    {
        try {
            $response = $this->chime->getMeeting([
                'MeetingId' => $project->meeting_id,
            ]);

            return $this->successResponse(data: $response['Meeting']);
        } catch (\Exception $e) {
            return $this->badRequestResponse(message: $e->getMessage());
        }
    }

    public function getAttendee(Project $project, Request $request): JsonResponse
    {
        try {
            $response = $this->chime->listAttendees([
                'MeetingId' => $project->meeting_id,
                'AttendeeId' => $request->user()->attendee_id,
            ]);

            return $this->successResponse(data: $response['Attendees']);
        } catch (\Exception $e) {
            return $this->badRequestResponse(message: $e->getMessage());
        }
    }

    public function listAttendees(Project $project): JsonResponse
    {
        try {
            $response = $this->chime->listAttendees([
                'MaxResults' => $this->projectRepository->getProjectMembersCount($project->id),
                'MeetingId' => $project->meeting_id,
            ]);

            return $this->successResponse(data: $response['Attendees']);
        } catch (\Exception $e) {
            return $this->badRequestResponse(message: $e->getMessage());
        }
    }

    public function deleteMeeting(Project $project): JsonResponse
    {
        try {
            $response = $this->chime->deleteMeeting([
                'MeetingId' => $project->meeting_id,
            ]);

            $this->projectRepository->update($project->id, ['meeting_status' => MeetingStatus::FALSE->value, 'meeting_id' => null]);

            return $this->successResponse(data: [
                'meeting_id' => $project->meeting_id,
                'delete_data' => $response,
            ]);
        } catch (\Exception $e) {
            return $this->badRequestResponse(message: $e->getMessage());
        }
    }

    public function deleteAttendee(Project $project, Request $request): JsonResponse
    {
        try {
            $response = $this->chime->deleteAttendee([
                'MeetingId' => $project->meeting_id,
                'AttendeeId' => $request->user()->attendee_id,
            ]);

            $this->userRepository->update($request->user()->id, ['attendee_id' => null]);

            return $this->successResponse(data: [
                'attendee_id' => $request->user()->attendee_id,
                'delete_data' => $response,
            ]);
        } catch (\Exception $e) {
            return $this->badRequestResponse(message: $e->getMessage());
        }
    }
}
