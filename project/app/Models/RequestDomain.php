<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class RequestDomain extends Model
{
    use HasFactory, Notifiable;
    protected $guard_name = 'web';
    protected $fillable = [
        'name',
        'email',
        'password',
        'type',
        'tenant_id',
        'domain_name',
        'created_at',
    ];
    public function payStatus()
    {
        return $this->hasOne('App\Models\Order', 'domainrequest_id', 'id');
    }
}
