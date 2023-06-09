<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminUserConversation extends Model
{
	public function user()
	{
	    return $this->belongsTo('App\Models\User')->withDefault();
	}

	public function admin()
	{
	    return $this->belongsTo('App\Models\Admin')->withDefault();
	}

	public function messages()
	{
	    return $this->hasMany('App\Models\AdminUserMessage','conversation_id');
	}

}
