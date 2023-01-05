<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
		//b9895e103f84f710907f353dd6a34e2b24deba40
		$hash_now = sha1(date('Y-m-d'));
		if($this->hash_key != $hash_now)
		{
			return false;
		}
		return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['required', 'max:30']
        ];
    }
}
