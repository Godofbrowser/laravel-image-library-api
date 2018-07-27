<?php

namespace App\Models;

use App\Repositories\TagsRepo;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = ['id', 'slug'];

	static function boot () {
		static::creating(function(self $model) {
			$model->name = strtolower($model->name);

			if (!isset($model->slug)) {
				/** @var TagsRepo $tagsRepo */
				$tagsRepo = app(TagsRepo::class);
				$model->slug = $tagsRepo->generateSlug($model);
			}
		});

		parent::boot();
	}

    public function images() {
    	return $this->belongsToMany(Image::class, 'image_tag', 'tag_id', 'image_id');
	}

}
