<?php
/**
 * Created by PhpStorm.
 * User: Emmy
 * Date: 7/27/2018
 * Time: 12:15 PM
 */

namespace App\Repositories;


use App\Models\Tag;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class TagsRepo
{
	public function getOrCreate(array $tagNames) {
		$tags = new EloquentCollection();

		foreach ($tagNames as $name) {
			$tags->push(tap(Tag::query()->firstOrNew([
				'name' => $name
			]), function (Tag $tag) use($name) {
				if (!$tag->exists) {
					$tag->slug = $this->generateSlug($name);
					$tag->save();
				}
			}));
		}

		return $tags;
	}

	public function generateSlug(string $name)
	{
		$count = 0;
		$name = strtolower($name);
		$name = preg_replace("/\s+/", '-', $name); // replace spaces with dash
		$name = preg_replace("/[^\d\w\-]/", '', $name); // remove characters not (digit, word or underscore, dash)
		$slug = $name . ($count ? "--$count" : '');

		while (Tag::query()->withoutGlobalScopes()->where('slug', $slug)->exists()) {
			$count++;
		}

		return $slug;
	}
}