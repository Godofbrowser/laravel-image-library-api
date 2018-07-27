<?php

namespace App\Models;

use App\Repositories\ImagesRepo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use N7olkachev\ComputedProperties\ComputedProperties;
use N7olkachev\ComputedProperties\ModelProxy;

class Image extends Model
{
	use ComputedProperties;

	const SCOPE_VISIBILITY = 'visibility';

	protected $guarded = ['id'];

    protected $casts = [
    	'dimension' => 'array',
		'flag_active' => 'boolean'
	];

    protected $appends = [
    	'url'
	];

    static function boot () {
    	static::deleting(function(self $model) {
			/** @var ImagesRepo $imageRepo */
			$imageRepo = app(ImagesRepo::class);
			$imageRepo->deleteFile($model);
		});

    	static::addGlobalScope(self::SCOPE_VISIBILITY, function(Builder $builder) {
    		$builder->where('flag_private', false);
		});

    	parent::boot();
	}

	/* RELATIONS */
	public function tags() {
		return $this->belongsToMany(
			Tag::class,
			'image_tag',
			'image_id',
			'tag_id'
		);
	}

	/* ATTRIBUTES */
	public function getUrlAttribute() {
		$config = config('site.uploads.images');
		$path = $config['original']['path'];
		return Storage::disk(config('filesystems.cloud'))
			->url($path . $this->getAttributeValue('filename'));
	}

	/* COMPUTED PROPERTIES */
	public function computedIsOwner(ModelProxy $model) {
		/** @var \App\Models\User $user */
		$user = current_auth_user();

		if (is_null($user))
			return DB::query()->selectRaw('false');

		logger($user);

		$tempTableName = self::getTable() .'_'. str_random(7);
		$query = DB::query()
			->from(self::getTable() .' as '. $tempTableName)
			->where(function (QueryBuilder $q) use($model, $tempTableName) {
				$q->whereRaw($tempTableName .'.'. self::getKeyName() .' = ' . $model->id);
			})
			->where('user_id', $user->getKey())
			->take(1);

		return DB::query()
			->selectRaw('EXISTS('.$query->toSql().')')
			->mergeBindings($query);
	}
}
