<?php

namespace Ekersten\Resizer;

use Intervention\Image\ImageManagerStatic as Image;

class Resizer
{
    public static function __callstatic($name, $args)
    {
        $file = array_pop($args);
        $is_external = preg_match('/http(s?):\/\//m', $file) > 0 ? true : false;

        $filename = basename($file);

        $cacheFolder = 'img_cache';
        $extension = pathinfo($file)['extension'];

        if (strtolower($extension) == 'svg') {
            return $file;
        }

        if (strtolower($extension) == 'gif') {
            return $file;
        }

        if ($is_external) {
            $filename = md5($file) . '.' . pathinfo($file)['extension'];
        }

        $unique_name = implode('_', array_merge([$name], $args, [str_slug(str_replace('/', '_', $file))])) . ".{$extension}";

        if (file_exists(public_path('img_cache/' . $unique_name))) {
            return url('img_cache/' . $unique_name);
        } else {
            if ($is_external) {
                $local_file = storage_path('img_cache/' . str_slug($file));
                if (!file_exists($local_file)) {
                    $remote = file_get_contents($file);
                    file_put_contents($local_file, $remote);
                }
            } else {
                if (file_exists(public_path($file))) {
                    $local_file = $file;
                } else if (file_exists(public_path('uploads/' . $file))) {
                    $local_file = public_path('uploads/' . $file);
                } else {
                    return '';
                }
            }

            // if (file_exists($local_file)) {
            $img = Image::make($local_file)->$name(...$args);
            $img->save(public_path('img_cache/' . $unique_name));
            // } else {
            //     return '';
            // }
        }

        return url('img_cache/' . $unique_name);
    }
}