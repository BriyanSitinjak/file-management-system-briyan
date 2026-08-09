<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Orchestrates file metadata persistence, disk storage, downloads, and activity logging.
 */
class FileService
{
    /**
     * List files, optionally scoped by folder, department, and title search.
     *
     * @param  array{folder_id?: int, department_id?: int, q?: string}  $filters
     * @return Collection<int, File>
     */
    public function list(array $filters = []): Collection
    {
        return File::query()
            ->with(['department', 'folder', 'user'])
            ->when(isset($filters['folder_id']), fn ($query) => $query->where('folder_id', $filters['folder_id']))
            ->when(isset($filters['department_id']), fn ($query) => $query->where('department_id', $filters['department_id']))
            ->when(
                filled($filters['q'] ?? null),
                fn ($query) => $query->where('title', 'like', '%'.$filters['q'].'%'),
            )
            ->latest()
            ->get();
    }

    /**
     * Create a file record from validated upload input for the given actor.
     *
     * @param  array{title: string, department_id: int, folder_id: int, file: UploadedFile}  $data
     * @return File The newly created file model with relations loaded.
     *
     * Side effects: stores the binary under the local disk files directory and writes an activity log entry.
     */
    public function store(array $data, User $user): File
    {
        /** @var UploadedFile $upload */
        $upload = $data['file'];
        $path = $upload->store('files');

        $file = File::query()->create([
            'title' => $data['title'],
            'department_id' => $data['department_id'],
            'folder_id' => $data['folder_id'],
            'user_id' => $user->id,
            'path' => $path,
            'original_name' => $upload->getClientOriginalName(),
            'mime_type' => $upload->getClientMimeType(),
            'size' => $upload->getSize(),
        ]);

        $this->log($user, 'file.uploaded', $file, "Uploaded file {$file->title}");

        return $file->load(['department', 'folder', 'user']);
    }

    /**
     * Return a single file with its related department, folder, and owner.
     *
     * @return File The file model with relations loaded.
     */
    public function show(File $file): File
    {
        return $file->loadMissing(['department', 'folder', 'user']);
    }

    /**
     * Update file metadata and optionally replace the stored binary for the given actor.
     *
     * @param  array{title?: string, department_id?: int, folder_id?: int, file?: UploadedFile}  $data
     * @return File The updated file model with relations loaded.
     *
     * Side effects: may replace the binary on disk and writes an activity log entry.
     */
    public function update(File $file, array $data, User $user): File
    {
        if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
            if ($file->path) {
                Storage::delete($file->path);
            }

            $upload = $data['file'];
            $data['path'] = $upload->store('files');
            $data['original_name'] = $upload->getClientOriginalName();
            $data['mime_type'] = $upload->getClientMimeType();
            $data['size'] = $upload->getSize();
            unset($data['file']);
        }

        $file->update($data);

        $this->log($user, 'file.updated', $file, "Updated file {$file->title}");

        return $file->fresh(['department', 'folder', 'user']);
    }

    /**
     * Soft-delete a file record and remove its binary for the given actor.
     *
     * Side effects: deletes the stored file from disk and writes an activity log entry.
     */
    public function delete(File $file, User $user): void
    {
        $title = $file->title;

        if ($file->path) {
            Storage::delete($file->path);
        }

        $file->delete();

        $this->log($user, 'file.deleted', $file, "Deleted file {$title}");
    }

    /**
     * Stream the stored binary to the client as a download for the given actor.
     *
     * @return StreamedResponse Download response for the file binary.
     *
     * Side effects: writes an activity log entry for the download action.
     */
    public function download(File $file, User $user): StreamedResponse
    {
        $this->log($user, 'file.downloaded', $file, "Downloaded file {$file->title}");

        return Storage::download($file->path, $file->original_name ?? $file->title);
    }

    /**
     * Persist a polymorphic activity log row for a file action.
     */
    private function log(User $user, string $action, File $file, string $description): void
    {
        ActivityLog::query()->create([
            'user_id' => $user->id,
            'action' => $action,
            'subject_type' => $file->getMorphClass(),
            'subject_id' => $file->id,
            'description' => $description,
        ]);
    }
}
