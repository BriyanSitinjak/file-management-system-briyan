<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileService
{
    public function __construct(
        private FolderService $folders,
        private ActivityLogger $activity,
    ) {}

    /**
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
     * @param  array{title: string, department_id: int, folder_id: int, file: UploadedFile}  $data
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

        $this->activity->log($user, 'file.uploaded', $file, "Uploaded file {$file->title}");

        return $file->load(['department', 'folder', 'user']);
    }

    /**
     * @return array{file: File, breadcrumbs: list<array{id: int|null, name: string, type?: string}>}
     */
    public function show(File $file): array
    {
        $file->loadMissing(['department', 'folder.parent', 'user']);

        $breadcrumbs = $file->folder
            ? $this->folders->breadcrumbsFor($file->folder)
            : [['id' => null, 'name' => 'Root']];

        $breadcrumbs[] = [
            'id' => $file->id,
            'name' => $file->title,
            'type' => 'file',
        ];

        return [
            'file' => $file,
            'breadcrumbs' => $breadcrumbs,
        ];
    }

    /**
     * @param  array{title?: string, department_id?: int, folder_id?: int, file?: UploadedFile}  $data
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

        $this->activity->log($user, 'file.updated', $file, "Updated file {$file->title}");

        return $file->fresh(['department', 'folder', 'user']);
    }

    public function delete(File $file, User $user): void
    {
        $title = $file->title;
        $file->delete();
        $this->activity->log($user, 'file.deleted', $file, "Deleted file {$title}");
    }

    public function restore(File $file, User $user): File
    {
        $file->restore();
        $this->activity->log($user, 'file.restored', $file, "Restored file {$file->title}");

        return $file->fresh(['department', 'folder', 'user']);
    }

    public function download(File $file, User $user): StreamedResponse
    {
        $this->activity->log($user, 'file.downloaded', $file, "Downloaded file {$file->title}");

        return Storage::download($file->path, $file->original_name ?? $file->title);
    }

    public function preview(File $file, User $user): Response
    {
        $this->activity->log($user, 'file.previewed', $file, "Previewed file {$file->title}");

        return Storage::response(
            $file->path,
            $file->original_name ?? $file->title,
            [
                'Content-Type' => $file->mime_type ?: 'application/octet-stream',
            ],
        );
    }
}
