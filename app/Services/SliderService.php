<?php

namespace App\Services;

use App\Models\Slider;

class SliderService
{
    public function all()
    {
        if(request('lang')){
            return Slider::with('media')->where('lang', request('lang', 'fa'))->get();
        }
        return Slider::with('media')->get();
    }

    public function create(array $data)
    {
        $data['meta'] = json_encode($data['meta'] ?? []);
        return Slider::create($data);
    }

    public function find($id)
    {
        return Slider::with('media')->findOrFail($id);
    }

    public function update($id, array $data)
    {
        $slider = Slider::findOrFail($id);
        $slider->update($data);
        return $slider;
    }

    public function delete($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->delete();
    }
} 