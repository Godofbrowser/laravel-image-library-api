<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ApiException;
use App\Models\Image;
use App\Repositories\ImagesRepo;
use App\Repositories\TagsRepo;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
	/**
	 * @var \App\Repositories\ImagesRepo
	 */
	private $imagesRepo;
	/**
	 * @var \App\Repositories\TagsRepo
	 */
	private $tagsRepo;

	/**
	 * ImageController constructor.
	 *
	 * @param \App\Repositories\ImagesRepo $imagesRepo
	 * @param \App\Repositories\TagsRepo $tagsRepo
	 */
	public function __construct(ImagesRepo $imagesRepo, TagsRepo $tagsRepo)
	{
		$this->imagesRepo = $imagesRepo;
		$this->tagsRepo = $tagsRepo;
	}

	public function getUserImages(Request $request) {
		$user = current_auth_user();

		$images = $user->images()->getQuery()
			->withoutGlobalScope(Image::SCOPE_VISIBILITY)
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
			->take(25)
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

        $this->validate($request, [
        	'name' => 'string|max:120',
        	'image_url' => 'sometimes|nullable|url',
        	'image' => 'required_without:image_url|image|max:5000',
		]);

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

		/** @var Image $image */
		$image = $this->imagesRepo->create(
			$user,
			$request->input('name'),
			$filename,
			$attributes['width'],
			$attributes['height'],
			$attributes['size'],
			($request->input('visibility') !== 'public')
		);

		return response()->json([
			'status' => 'success',
			'info' => 'upload successful',
			'data' => $image->makeVisible(['id'])
		]);
    }

    public function update(Request $request, $id) {
		$user = current_auth_user();

		$this->validate($request, [
			'name' => 'required|string|max:120',
			'visibility' => 'required|in:public,private',
			'tags' => 'array|max:10'
		]);

		/** @var Image $image */
		$image = $user->images()
			->getQuery()
			->withoutGlobalScope(Image::SCOPE_VISIBILITY)
			->whereKey($id)
			->first();

		if (!$image) throw ApiException::runtimeException('Image not found');

		$attributes = [
			'name' => $request->input('name'),
			'flag_private' => $request->input('visibility') !== 'public'
		];

		$tags = $request->input('tags', null);
		if ($tags && is_array($tags)) {
			$tagnames = collect($tags)->map(function($tag) {
				return is_array($tag) ? (isset($tag['text']) ? $tag['text'] : $tag) : $tag;
			})->toArray();
			$tags = $this->tagsRepo->getOrCreate($tagnames);
		}

		$this->imagesRepo->update($image, $attributes, $tags);

		return response()->json([
			'status' => 'success',
			'info' => 'Update successful',
			'data' => $image->makeVisible(['id'])
		]);
	}
}
