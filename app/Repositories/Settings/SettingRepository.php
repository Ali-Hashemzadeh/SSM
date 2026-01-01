<?php

namespace App\Repositories\Settings;

use App\Models\Setting;

class SettingRepository implements SettingRepositoryInterface
{
    public function find($id)
    {
        return Setting::findOrFail($id);
    }

    public function getFirst()
    {
        return Setting::first() ?? new Setting();
    }

    public function update($id, array $data)
    {
        $setting = Setting::updateOrCreate(['id' => $id], $data);
        return $setting;
    }

    public function updateFirst(array $data)
    {
        $setting = Setting::first();
        if ($setting) {
            $setting->update($data);
        } else {
            $setting = Setting::create($data);
        }
        return $setting;
    }
}