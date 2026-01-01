<?php

namespace App\Repositories\Medias;

use App\Models\Media;

class MediaRepository implements MediaRepositoryInterface
{
    public function all($perPage = 15, array $filters = [])
    {
        $filters = array_filter($filters, function($v) { return $v !== null && $v !== ''; });
        $query = Media::query();
        // Search by alt_text
        if (!empty($filters['s'])) {
            $search = $filters['s'];
            $query->where(function($q) use ($search) {
                $q->where('alt_text', 'like', "%$search%");
            });
        }
        // Filter by uploader_id
        if (isset($filters['uploader_id'])) {
            $query->where('uploader_id', $filters['uploader_id']);
        }
        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
        return $query->paginate($perPage);
    }

    public function find($id)
    {
        return Media::with(['uploader', 'posts'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Media::create($data);
    }

    public function update($id, array $data)
    {
        $media = Media::findOrFail($id);
        $media->update($data);
        return $media;
    }

    public function delete($id)
    {
        $media = Media::findOrFail($id);
        $media->delete();
        return true;
    }
} 