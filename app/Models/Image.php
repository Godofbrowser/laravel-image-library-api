<?php

namespace App\Models;

use App\Repositories\ImagesRepo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use N7olkachev\ComputedProperties\ComputedProperties;
use N7olkachev\ComputedProperties\ModelProxy;

class Image extends Model
{
	use ComputedProperties;

	protected $guarded = ['id'];

    protected $casts = [
    	'dimension' => 'array'
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

    	parent::boot();
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

		$tempTableName = self::getTable() .'_'. str_random(7);
		$query = self::query()
			->from(self::getTable() .' as '. $tempTableName)
			->where(function (Builder $q) use($model, $tempTableName) {
				$q->whereRaw($tempTableName .'.'. self::getKeyName() .' = ' . $model->id);
			})
			->where('user_id', $user->getKey())
			->take(1);

		log_all($tempTableName);

		return DB::query()
			->selectRaw('EXISTS('.$query->toSql().')')
			->mergeBindings($query->getQuery());
	}
}
