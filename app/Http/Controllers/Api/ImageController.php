<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\Image;
use App\Repositories\ImagesRepo;
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
		$user = $request->user();

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
		$user = $request->user();

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
		$user = $request->user();

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
		
		logger($request->all());
		logger('name: ' . $request->input('name'));

        $v = validator($request->all(), [
        	'name' => 'required|string|max:25',
        	'image' => 'required|image|max:5000'
		]);

        if ($v->fails())
        	throw ApiException::runtimeException($v->errors()->first());

		$uploadedFile = $request->file('image');

		$filename = $this->imagesRepo->storeFile($uploadedFile);
		$attributes = $this->imagesRepo->extractImageInfo($uploadedFile);

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
