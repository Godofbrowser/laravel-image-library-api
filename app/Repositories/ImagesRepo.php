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
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image as IntervImage;

class ImagesRepo {
	/**
	 * @var \App\Models\Image
	 */
	private $image;

	/**
	 * ImagesRepo constructor.
	 *
	 * @param \App\Models\Image $image
	 */
	public function __construct(Image $image)
	{
		$this->image = $image;
	}

	public function create(User $user, $name, $filename, $width, $height, $size, $private = false) {
		/** @var Image $image */
		$image = $this->image->create([
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

		return $image;
	}

	public function extractImageInfo(UploadedFile $file) {
		/** @var \Intervention\Image\Image $image */
		$image = IntervImage::make($file->getRealPath());

		return [
			'width' => $image->width(),
			'height' => $image->height(),
			'size' => $file->getSize()
		];
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
		Storage::disk(config('filesystem.cloud'))->put($path . $fileName, $resource);
		return $fileName;
	}

	public function deleteFile(Image $model) {
		$config = config('site.uploads.images');
		$path = $config['original']['path'];

		Storage::disk(config('filesystem.cloud'))
			->delete($path . $model->getAttributeValue('filename'));
	}
}