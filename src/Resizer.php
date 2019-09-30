<?php

namespace Ekersten\Resizer;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Intervention\Image\ImageManagerStatic as Image;

class Resizer
{

    private $ignored_extensions = ['svg', 'gif'];

    public function __call($name, $args)
    {
        
        $this->original_file = array_pop($args);
        $this->method_name = $name;
        $this->extension = pathinfo($this->original_file)['extension'];

        if (in_array($this->extension, $this->ignored_extensions)) {
            return $this->original_file;
        }

        $unique_name = $this->getUniqueName($args);

        $generated_file = $this->getStoragePath() . DIRECTORY_SEPARATOR . $unique_name;

        $local_file_path = 'public' . DIRECTORY_SEPARATOR . config('resizer.storage_folder') . DIRECTORY_SEPARATOR . $unique_name;

        if (Storage::exists($local_file_path) && time() - Storage::lastModified($local_file_path) < config('resizer.max_ttl')) {
            return Storage::url(config('resizer.storage_folder') . '/' . $unique_name);
        } else {
            if (preg_match('/http(s?):\/\//m', $this->original_file) > 0 ? true : false) {
                $local_file = $this->downloadExternalFile();
            } else {
                $local_file = $this->getLocalFilePath();
                if($local_file === false) {
                    return $this->original_file;
                }
            }
            $method_name = $this->method_name;
            $new_image = Image::make($local_file)->$method_name(...$args);
            $new_image->save($generated_file);
        }
        
        return Storage::url(config('resizer.storage_folder') . '/' . $unique_name);

    }

    private function getStoragePath()
    {
        Artisan::call('storage:link');
        $target_path = 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . config('resizer.storage_folder');
        if (!Storage::exists($target_path)) {
            Storage::makeDirectory($target_path);
        }

        return storage_path($target_path);
    }

    private function getUniqueName($args)
    {
        $unique_name = str_replace(".{$this->extension}", '', $this->original_file);
        $unique_name = str_replace('/', '_', $unique_name);
        $unique_name = str_replace('__', '_', $unique_name);
        $unique_name = str_slug($unique_name, '_');
        $unique_name = implode('x', $args) . '_' . $unique_name;
        $unique_name = $this->method_name . '_' . $unique_name;
        $unique_name = $unique_name . ".{$this->extension}";

        return $unique_name;
    }

    private function downloadExternalFile() {
        $external_filename = md5($this->original_file) . '.' . $this->extension;
        $local_file = $this->getStoragePath() . DIRECTORY_SEPARATOR . $external_filename;
        copy($this->original_file, $local_file);

        return $local_file;
    }

    private function getLocalFilePath()
    {
        if (file_exists(realpath(public_path($this->original_file)))) {
            return realpath(public_path($this->original_file));
        } else if (file_exists(realpath(storage_path($this->original_file)))) {
            return realpath(storage_path($this->original_file));
        } else {
            return false;
        }
    }
}