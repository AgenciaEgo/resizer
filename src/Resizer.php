<?php

namespace Ekersten\Resizer;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;

class Resizer
{

    private $ignored_extensions = ['svg', 'gif'];

    public function __call($name, $args)
    {
        $this->invalidate_cache = false;

        $last_param = array_pop($args);
        if (gettype($last_param) === 'boolean') {
            $this->invalidate_cache = $last_param;
            $this->original_file = array_pop($args);
        } else {
            $this->original_file = $last_param;
        }
        $this->method_name = $name;
        $this->extension = pathinfo($this->original_file)['extension'];

        if (in_array($this->extension, $this->ignored_extensions)) {
            return $this->original_file;
        }

        $unique_name = $this->getUniqueName($args);

        $generated_file = implode(DIRECTORY_SEPARATOR, [$this->getStoragePath(), $unique_name]);

        $local_file_path = implode(DIRECTORY_SEPARATOR, ['public', config('resizer.storage_folder'), $unique_name]);

        if (Storage::exists($local_file_path) && $this->invalidate_cache) {
            Storage::delete($local_file_path);
        }


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
            if ($method_name === 'resize') {
                $new_image = Image::make($local_file)->resize($args[0], $args[1], function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else {
                $new_image = Image::make($local_file)->$method_name(...$args);
            }
            $new_image->save($generated_file);
        }
        
        return Storage::url(config('resizer.storage_folder') . '/' . $unique_name);

    }

    private function getStoragePath()
    {
        Artisan::call('storage:link');
        if (!Storage::exists(implode(DIRECTORY_SEPARATOR, ['app', 'public', 'resizer']))) {
            Storage::makeDirectory(implode(DIRECTORY_SEPARATOR, ['public', 'resizer']));
        }

        return storage_path(implode(DIRECTORY_SEPARATOR, ['app', 'public', 'resizer']));
    }

    private function getUniqueName($args)
    {
        $unique_name = str_replace(".{$this->extension}", '', $this->original_file);
        $unique_name = str_replace('/', '_', $unique_name);
        $unique_name = str_replace('__', '_', $unique_name);
        $unique_name = Str::slug($unique_name, '_');
        $unique_name = implode('x', $args) . '_' . $unique_name;
        $unique_name = $this->method_name . '_' . $unique_name;
        $unique_name = $unique_name . ".{$this->extension}";

        return $unique_name;
    }

    private function downloadExternalFile() {
        $external_filename = md5($this->original_file) . '.' . $this->extension;
        $local_file = implode(DIRECTORY_SEPARATOR, [$this->getStoragePath(), $external_filename]);
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