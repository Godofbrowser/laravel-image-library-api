<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\Image;
use App\Repositories\ImagesRepo;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
	/**
	 * @var \App\Repositories\ImagesRepo
	 */
	private $imagesRepo;

	/**
	 * ImageController constructor.
	 *
	 * @param \App\Repositories\ImagesRepo $imagesRepo
	 */
	public function __construct(ImagesRepo $imagesRepo)
	{
		$this->imagesRepo = $imagesRepo;
	}

	public function getUserImages(Request $request) {
		$user = current_auth_user();

		$images = $user->images()->getQuery()
			->withComputed(['is_owner'])
			->latest()
			->get(12);

		return response()->json([
			'status' => 'success',
			'info' => 'Images retrieved',
			'data' => $images
		]);
	}

	public function getRecent(Request $request) {
		$user = current_auth_user();

		$images = Image::query()
			->withComputed(['is_owner'])
			->latest()
			->take(12)
			->get();

		return response()->json([
			'status' => 'success',
			'info' => 'Images retrieved',
			'data' => $images
		]);
	}

	public function getAllUploads(Request $request) {
		$user = current_auth_user();

		$query = Image::query()
			->withComputed(['is_owner'])
			->latest()
			->take(12);

		if ($request->has('search')){
			$query->where('name', 'like', '%' . $request->get('search') . '%');
		}

		$images = $query->get();

		return response()->json([
			'status' => 'success',
			'info' => 'Images retrieved',
			'data' => $images
		]);
	}

	public function upload (Request $request) {
		$user = current_auth_user();

        $v = validator($request->all(), [
        	'name' => 'required|string|max:25',
        	'image_url' => 'sometimes|nullable|url',
        	'image' => 'required_without:image_url|image|max:5000',
		]);

        if ($v->fails())
        	throw ApiException::runtimeException($v->errors()->first());

		$uploadedFile = $request->file('image');

        if($uploadedFile) {
			$attributes = $this->imagesRepo->extractImageInfo($uploadedFile);
			$filename = $this->imagesRepo->storeFile($uploadedFile);
		} else {
        	$image_url = $request->input('image_url');

        	try {
				$resource = file_get_contents($image_url);
				$filename = str_random(30) . '.png';
				$filePath = 'tmp/uploads/'. $filename;
			} catch (\Exception $e) {
        		throw ApiException::runtimeException($e->getMessage());
			}

			Storage::disk('local')
				->put(
					$filePath,
					$this->imagesRepo->streamImage($resource, 'png')
				);

        	$fullPath = config('filesystems.disks.local.root');
        	$fullPath .= DIRECTORY_SEPARATOR . $filePath;
        	$uploadedFile = new UploadedFile($fullPath, $filename);

			$attributes = $this->imagesRepo->extractImageInfo($uploadedFile);
			$filename = $this->imagesRepo->storeFile($uploadedFile);

			Storage::disk('local')->delete($filePath);
		}


		$image = $this->imagesRepo->create(
			$user,
			$request->input('name'),
			$filename,
			$attributes['width'],
			$attributes['height'],
			$attributes['size']
		);

		return response()->json([
			'status' => 'success',
			'info' => 'upload successful',
			'data' => $image
		]);
    }
}
