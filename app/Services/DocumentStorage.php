<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DocumentStorage
{
    private const GDRIVE_PREFIX = 'gdrive:';

    private const ROOT_FOLDER_NAME = 'Grobook BPS Sumsel';

    public function isGoogleDrivePath(string $path): bool
    {
        return str_starts_with($path, self::GDRIVE_PREFIX);
    }

    public function getDriveViewUrl(string $path): ?string
    {
        if (! $this->isGoogleDrivePath($path)) {
            return null;
        }

        $fileId = substr($path, strlen(self::GDRIVE_PREFIX));

        return "https://drive.google.com/file/d/{$fileId}/view";
    }

    public function storeUploadedPdf(UploadedFile $file, string $category, string $fileName): string
    {
        return $this->storeContent(
            (string) file_get_contents($file->getRealPath()),
            $category,
            $fileName,
            $category.'/'.uniqid('', true).'-'.$fileName,
        );
    }

    public function storePdfBytes(string $content, string $category, string $fileName, string $localFallbackPath): string
    {
        return $this->storeContent($content, $category, $fileName, $localFallbackPath);
    }

    public function exists(string $path): bool
    {
        if (! $this->isGoogleDrivePath($path)) {
            return Storage::disk('local')->exists($path);
        }

        $token = $this->resolveAccessToken();
        if ($token === null) {
            return false;
        }

        $fileId = substr($path, strlen(self::GDRIVE_PREFIX));
        $response = Http::withToken($token)
            ->get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'fields' => 'id,trashed',
            ]);

        return $response->successful() && ! $response->json('trashed', false);
    }

    public function getContents(string $path): ?string
    {
        if (! $this->isGoogleDrivePath($path)) {
            return Storage::disk('local')->exists($path)
                ? Storage::disk('local')->get($path)
                : null;
        }

        $token = $this->resolveAccessToken();
        if ($token === null) {
            return null;
        }

        $fileId = substr($path, strlen(self::GDRIVE_PREFIX));
        $response = Http::withToken($token)
            ->get("https://www.googleapis.com/drive/v3/files/{$fileId}", [
                'alt' => 'media',
            ]);

        return $response->successful() ? $response->body() : null;
    }

    private function storeContent(string $content, string $category, string $fileName, string $localFallbackPath): string
    {
        $token = $this->resolveAccessToken();

        if ($token !== null) {
            $driveFileId = $this->uploadToGoogleDrive($token, $content, $category, $fileName);
            if ($driveFileId !== null) {
                return self::GDRIVE_PREFIX.$driveFileId;
            }
        }

        Storage::disk('local')->put($localFallbackPath, $content);

        return $localFallbackPath;
    }

    private function uploadToGoogleDrive(string $token, string $content, string $category, string $fileName): ?string
    {
        $parentId = $this->ensureFolder($token, self::ROOT_FOLDER_NAME, null);
        if ($parentId === null) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim($category, '/'))));
        if ($segments === []) {
            $segments = ['documents'];
        }

        foreach ($segments as $segment) {
            $parentId = $this->ensureFolder($token, $segment, $parentId);
            if ($parentId === null) {
                return null;
            }
        }

        $boundary = 'grobook_boundary_'.bin2hex(random_bytes(8));
        $metadata = json_encode([
            'name' => $fileName,
            'parents' => [$parentId],
            'mimeType' => 'application/pdf',
        ]);

        $multipartBody = "--{$boundary}\r\n"
            ."Content-Type: application/json; charset=UTF-8\r\n\r\n"
            ."{$metadata}\r\n"
            ."--{$boundary}\r\n"
            ."Content-Type: application/pdf\r\n\r\n"
            ."{$content}\r\n"
            ."--{$boundary}--";

        $response = Http::withToken($token)
            ->withBody($multipartBody, "multipart/related; boundary={$boundary}")
            ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id');

        if (! $response->successful()) {
            return null;
        }

        $fileId = $response->json('id');
        if (is_string($fileId) && $fileId !== '') {
            Http::withToken($token)->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
                'role' => 'reader',
                'type' => 'anyone',
            ]);
        }

        return $fileId;
    }

    private function ensureFolder(string $token, string $folderName, ?string $parentId): ?string
    {
        $cacheKey = 'gdrive_folder_'.md5(($parentId ?? 'root').'/'.$folderName);
        $cachedId = Cache::get($cacheKey);

        if (is_string($cachedId) && $cachedId !== '') {
            $check = Http::withToken($token)->get("https://www.googleapis.com/drive/v3/files/{$cachedId}", [
                'fields' => 'id,trashed',
            ]);
            if ($check->successful() && ! $check->json('trashed', false)) {
                return $cachedId;
            }
            Cache::forget($cacheKey);
        }

        $escapedName = addslashes($folderName);
        $q = "mimeType='application/vnd.google-apps.folder' and name='{$escapedName}' and trashed=false";
        if ($parentId !== null) {
            $q .= " and '{$parentId}' in parents";
        }

        $search = Http::withToken($token)->get('https://www.googleapis.com/drive/v3/files', [
            'q' => $q,
            'fields' => 'files(id,name)',
            'pageSize' => 1,
        ]);

        if ($search->successful()) {
            $existingId = $search->json('files.0.id');
            if (is_string($existingId) && $existingId !== '') {
                Cache::forever($cacheKey, $existingId);

                return $existingId;
            }
        }

        $payload = [
            'name' => $folderName,
            'mimeType' => 'application/vnd.google-apps.folder',
        ];
        if ($parentId !== null) {
            $payload['parents'] = [$parentId];
        }

        $create = Http::withToken($token)->post('https://www.googleapis.com/drive/v3/files?fields=id', $payload);
        if (! $create->successful()) {
            return null;
        }

        $newId = $create->json('id');
        if (is_string($newId) && $newId !== '') {
            Cache::forever($cacheKey, $newId);

            return $newId;
        }

        return null;
    }

    private function resolveAccessToken(): ?string
    {
        // Prefer Ketua DLS / Petugas DLS token for centralized Drive storage, fallback to current user or any user with token
        $candidates = User::whereNotNull('google_refresh_token')
            ->orWhereNotNull('google_access_token')
            ->get()
            ->sortBy(function (User $u) {
                if ($u->hasAssignedRole(Role::KetuaDls)) {
                    return 0;
                }
                if ($u->hasAssignedRole(Role::PetugasDls)) {
                    return 1;
                }
                if (auth()->id() === $u->id) {
                    return 2;
                }

                return 3;
            });

        foreach ($candidates as $user) {
            $token = $this->getValidTokenForUser($user);
            if ($token !== null) {
                return $token;
            }
        }

        return null;
    }

    private function getValidTokenForUser(User $user): ?string
    {
        $notExpired = $user->google_token_expires_at === null
            || $user->google_token_expires_at->isAfter(now()->addMinute());

        if (! empty($user->google_access_token) && $notExpired) {
            return $user->google_access_token;
        }

        if (empty($user->google_refresh_token)) {
            return ! empty($user->google_access_token) ? $user->google_access_token : null;
        }

        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return ! empty($user->google_access_token) ? $user->google_access_token : null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $user->google_refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! $response->successful()) {
            return null;
        }

        $newAccessToken = $response->json('access_token');
        $expiresIn = (int) $response->json('expires_in', 3599);

        if (empty($newAccessToken)) {
            return null;
        }

        $user->update([
            'google_access_token' => $newAccessToken,
            'google_token_expires_at' => now()->addSeconds($expiresIn),
        ]);

        return $newAccessToken;
    }
}
