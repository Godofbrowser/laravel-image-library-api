<?php

namespace App\Models;

use App\Repositories\ImagesRepo;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
	protected $guarded = ['id'];

	static function boot () {
		static::created(function(self $model) {
			// Compute parent's rating
			if ($image = $model->getRelationValue('image')) {
				/** @var \App\Repositories\ImagesRepo $imageRepo */
				$imageRepo = app(ImagesRepo::class);
				$imageRepo->computeRating($image);
			}
		});

		parent::boot();
	}

	public function image() {
		return $this->belongsTo(Image::class, 'image_id', 'id');
	}
}
