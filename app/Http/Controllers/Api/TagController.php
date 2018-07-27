<?php

namespace App\Http\Controllers\Api;

use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    public function getAllTags() {
    	$tags = Tag::query()->take(25)->get();

    	return response()->json([
    		'status' => 'success',
			'data' => $tags
		]);
	}

    public function getTagImages(Request $request, $slug) {
    	$tag = Tag::query()->where('slug', strtolower($slug))->firstorfail();

    	$images = $tag->images()->getQuery()
			->with('tags')
			->withComputed(['is_owner'])
//			->forPage(1, 12)
			->take(12)
			->get();

    	return response()->json([
    		'status' => 'success',
			'data' => [
				'tag' => $tag,
				'images' => $images
			]
		]);
	}
}
