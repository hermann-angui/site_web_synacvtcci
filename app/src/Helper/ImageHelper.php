<?php

namespace App\Helper;

class ImageHelper
{

    public function createThumbnail($source_file, $thumbpath, $thumbnail_width, $thumbnail_height, $background=false) : ?string{

        $d = gd_info();

        list($original_width, $original_height, $original_type) = getimagesize($source_file);
        if ($original_width > $original_height) {
            $new_width = $thumbnail_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $thumbnail_height;
            $new_width = intval($original_width * $new_height / $original_height);
        }
        $dest_x = intval(($thumbnail_width - $new_width) / 2);
        $dest_y = intval(($thumbnail_height - $new_height) / 2);


        if ($original_type === 1) {
            $imgt = "imagegif";
            $fileName =  $thumbpath . 'thumbnail.gif';
            $imgcreatefrom = "imagecreatefromgif";
        } else if ($original_type === 2) {
            $imgt = "imagejpeg";
            $quality = 100;
            $fileName =  $thumbpath . 'thumbnail.jpg';
            $imgcreatefrom = "imagecreatefromjpeg";
        } else if ($original_type === 3) {
            $imgt = "imagepng";
            $quality = 9;
            $fileName = $thumbpath . 'thumbnail.png';
            $imgcreatefrom = "imagecreatefrompng";
        } else {
            return null;
        }

        $old_image = $imgcreatefrom($source_file);
        $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height); // creates new image, but with a black background

        // figuring out the color for the background
        if(is_array($background) && count($background) === 3) {
            list($red, $green, $blue) = $background;
            $color = imagecolorallocate($new_image, $red, $green, $blue);
            imagefill($new_image, 0, 0, $color);
            // apply transparent background only if is a png image
        } else if($background === 'transparent' && $original_type === 3) {
            imagesavealpha($new_image, true);
            $color = imagecolorallocatealpha($new_image, 0, 0, 0, 127);
            imagefill($new_image, 0, 0, $color);
        }

        imagecopyresampled($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
        //$imgt($new_image, $thumbpath . 'thumbnail.jpg', $thumbnail_width, $thumbnail_height, $background);
        $imgt($new_image, $fileName, $quality);
        return (file_exists($fileName)) ? $fileName : null;
    }

}
