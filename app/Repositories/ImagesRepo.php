<?php
/**
 * Created by PhpStorm.
 * User: Emmy
 * Date: 7/23/2018
 * Time: 7:55 PM
 */

namespace App\Repositories;

use App\Models\Image;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as IntervImage;

class ImagesRepo {

	public function create(User $user, $name, $filename, $width, $height, $size, $private = true, EloquentCollection $tags = null) {
		/** @var Image $image */
		$image = Image::query()->create([
			'user_id' => $user->getKey(),
			'name' => $name,
			'filename' => $filename,
			'size' => $size,
			'flag_private' => $private,
			'dimension' => [
				'width' => $width,
				'height' => $height,
			]
		]);

		$this->updateTags($image, $tags);

		return $image;
	}

	public function update(Image $image, $attributes, EloquentCollection $tags = null) {
		$image->update([
			'name' => array_get($attributes, 'name', $image->getAttributeValue('name')),
			'flag_private' => (bool) array_get($attributes, 'flag_private', $image->getAttribute('flag_private'))
		]);

		$this->updateTags($image, $tags);

		return $image;
	}

	public static function updateTags(Image $image, EloquentCollection $tags = null) {
		if ($tags && $tags->isNotEmpty()) {
			// attach and/or detach tags here
			$image->tags()->sync($tags);
		}
	}

	public function extractImageInfo($file) {
		/** @var \Intervention\Image\Image $image */

		if ($file instanceof UploadedFile){
			$image = IntervImage::make($file->getRealPath());
		} else {
			$image = IntervImage::make($file);
		}

		return [
			'width' => $image->width(),
			'height' => $image->height(),
			'size' => $file->getSize()
		];
	}

	public function streamImage($resource, $format = 'png', $quality = 90) {
		/** @var \Intervention\Image\Image $image */
		$image = IntervImage::make($resource);

		return $image->stream($format, $quality);
	}

	public function storeFile(UploadedFile $uploadedFile){
		$config = config('site.uploads.images');
		$path = $config['original']['path'];

		$fileName = sprintf(
			'%s.%s.%s',
			str_random(5),
			str_random(20),
			$uploadedFile->guessClientExtension()
		);

		$resource = file_get_contents($uploadedFile->getRealPath());
		Storage::disk(config('filesystems.cloud'))->put($path . $fileName, $resource, 'public');
		return $fileName;
	}

	public function deleteFile(Image $model) {
		$config = config('site.uploads.images');
		$path = $config['original']['path'];

		Storage::disk(config('filesystems.cloud'))
			->delete($path . $model->getAttributeValue('filename'));
	}
}