<?php

namespace App\Repositories\Settings;

interface SettingRepositoryInterface
{
    public function find($id);
    public function getFirst();
    public function update($id, array $data);
    public function updateFirst(array $data);
}