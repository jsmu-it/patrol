<?php

namespace App\Services;

use App\Models\User;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    private ?ServiceAccountCredentials $credentials = null;

    private ?string $projectId = null;

    public function __construct()
    {
        $this->projectId = config('services.fcm.project_id');

        $credentialsPath = config('services.fcm.credentials');
        if (! $credentialsPath || ! is_readable($credentialsPath)) {
            // Dicatat sebagai error, bukan warning: level log server ini
            // 'error', sehingga peringatan tidak pernah muncul dan kegagalan
            // kirim notifikasi berlalu tanpa jejak sama sekali.
            Log::error('FIREBASE_CREDENTIALS belum disetel atau tidak terbaca; notifikasi HP tidak akan terkirim.');
            return;
        }

        $this->credentials = new ServiceAccountCredentials(
            ['https://www.googleapis.com/auth/firebase.messaging'],
            $credentialsPath,
        );
    }

    private function getAccessToken(): ?string
    {
        if (! $this->credentials) {
            return null;
        }

        try {
            $httpHandler = HttpHandlerFactory::build();
            $token = $this->credentials->fetchAuthToken($httpHandler);

            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            Log::error('Failed to fetch FCM access token', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_filter(array_unique($tokens)));
        if (empty($tokens)) {
            return;
        }

        if (! $this->projectId) {
            Log::error('FIREBASE_PROJECT_ID belum disetel; notifikasi HP tidak akan terkirim.');
            return;
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            Log::error('Gagal mengambil token akses FCM; notifikasi HP tidak akan terkirim.');
            return;
        }

        $endpoint = sprintf(
            'https://fcm.googleapis.com/v1/projects/%s/messages:send',
            $this->projectId,
        );

        // data payload FCM HTTP v1 harus string->string
        $stringData = [];
        foreach ($data as $key => $value) {
            $stringData[$key] = (string) $value;
        }

        foreach ($tokens as $token) {
            try {
                Http::withToken($accessToken)
                    ->post($endpoint, [
                        'message' => [
                            'token' => $token,
                            'notification' => [
                                'title' => $title,
                                'body' => $body,
                            ],
                            'data' => $stringData,
                        ],
                    ])->throw();
            } catch (\Throwable $e) {
                Log::error('Failed to send FCM notification', [
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    public function notifyAdmins(string $title, string $body, array $data = [], ?int $projectId = null): void
    {
        $query = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN, User::ROLE_PROJECT_ADMIN, User::ROLE_HRD])
            ->whereNotNull('fcm_token');

        // If projectId is specified, filter to only admins with access to that project
        if ($projectId !== null) {
            $query->where(function ($q) use ($projectId) {
                // Superadmins and HRD always have access to all projects
                $q->whereIn('role', [User::ROLE_SUPERADMIN, User::ROLE_HRD])
                    // OR admins who have been assigned to this specific project
                    ->orWhereHas('accessibleProjects', function ($projectQuery) use ($projectId) {
                        $projectQuery->where('projects.id', $projectId);
                    });
            });
        }

        $tokens = $query->pluck('fcm_token')->all();

        $this->sendToTokens($tokens, $title, $body, $data);
    }

    public function notifyUser(User $user, string $title, string $body, array $data = []): void
    {
        if (! $user->fcm_token) {
            return;
        }

        $this->sendToTokens([$user->fcm_token], $title, $body, $data);
    }
}
