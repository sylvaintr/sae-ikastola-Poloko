<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;


/**
 * Class Role
 * 
 * @property int $idRole
 * @property string $name
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @package App\Models
 */
class Role extends \Spatie\Permission\Models\Role
{
	protected $table = 'role';
	protected $primaryKey = 'idRole';
	public $incrementing = true;
	protected $keyType = 'int';

	protected $casts = [
		'idRole' => 'int'
	];

	protected $fillable = [
		'name',
		'guard_name'
	];

}
