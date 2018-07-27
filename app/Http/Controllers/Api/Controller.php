<?php
/**
 * Created by PhpStorm.
 * User: Emmy
 * Date: 7/27/2018
 * Time: 10:12 AM
 */

namespace App\Http\Controllers\Api;


use App\Exceptions\ApiException;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
	public function validate(Request $request, array $rules,
								array $messages = [], array $customAttributes = []){
		$v = $this->getValidationFactory()
			->make($request->all(), $rules, $messages, $customAttributes);

		if ($v->fails())
			throw ApiException::runtimeException($v->errors()->first());

		return $this->extractInputFromRules($request, $rules);
	}

}