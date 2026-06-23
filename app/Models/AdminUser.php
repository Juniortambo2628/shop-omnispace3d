<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $username
 * @property string $password_hash
 * @property string $display_name
 * @property string $role
 * @property bool $active
 * @property string $created_at
 */
class AdminUser extends Model
{
    public $timestamps = false;

    protected $table = 'admin_users';

    protected $fillable = [
        'username',
        'password_hash',
        'display_name',
        'role',
        'active',
        'created_at',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toLegacyArray(): array
    {
        return $this->attributesToArray();
    }

    public function passwordHash(): ?string
    {
        return $this->password_hash ?? ($this->getAttributes()['password'] ?? null);
    }
}
