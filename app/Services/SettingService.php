<?php

namespace App\Services;

use App\Repositories\Settings\SettingRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Services\FileUploadService;

class SettingService
{
    protected SettingRepositoryInterface $settingRepository;
    protected FileUploadService $fileUploadService;

    public function __construct(SettingRepositoryInterface $settingRepository, FileUploadService $fileUploadService)
    {
        $this->settingRepository = $settingRepository;
        $this->fileUploadService = $fileUploadService;
    }

    public function getSetting($id)
    {
        return $this->settingRepository->find($id);
    }

    /**
     * @param int $id
     * @param array $data (can contain 'logo' as UploadedFile)
     */
    public function updateSetting($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            // if (isset($data['logo']) && $data['logo'] instanceof \Illuminate\Http\UploadedFile) {
            //     $data['logo'] = $this->fileUploadService->upload($data['logo'], 'settings');
            // }
            // $data['title'] = json_encode($data['title']);
            // $data['description'] = json_encode($data['description']);
            return $this->settingRepository->update($id, $data);
        });
    }
} 