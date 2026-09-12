<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasPerawatNik;
use App\Traits\Uuids;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Uuids, HasPerawatNik;

    public const ROLE_KAMPUS = 'kampus';
    public const ROLE_LEMBAGA = 'lembaga';
    public const ROLE_FASKES = 'faskes';
    public const ROLE_MITRA = 'mitra';
    public const ROLE_HEADHUNTER = 'headhunter';
    public const ROLE_REGULATOR = 'regulator';
    public const ROLE_PERAWAT = 'perawat';
    public const ROLE_ADMIN = 'admin';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_SUSPENDED = 'suspended';

    public const ROLES = [
        self::ROLE_KAMPUS,
        self::ROLE_LEMBAGA,
        self::ROLE_FASKES,
        self::ROLE_MITRA,
        self::ROLE_HEADHUNTER,
        self::ROLE_REGULATOR,
        self::ROLE_PERAWAT,
        self::ROLE_ADMIN,
    ];

    public const REGISTRABLE_ROLES = [
        self::ROLE_KAMPUS,
        self::ROLE_LEMBAGA,
        self::ROLE_MITRA,
        self::ROLE_HEADHUNTER,
        self::ROLE_REGULATOR,
        self::ROLE_PERAWAT,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'status',
        'perawat_id',
        'perawat_nik',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function hasRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role, $roles, true);
    }

    public function perawat()
    {
        return $this->belongsTo(Perawat::class, 'perawat_nik', 'nik');
    }

    public function institusiPendidikan()
    {
        return $this->morphedByMany(InstitusiPendidikan::class, 'organisasiable', 'organisasi_user');
    }

    public function lembagaPelatihan()
    {
        return $this->morphedByMany(LembagaPelatihan::class, 'organisasiable', 'organisasi_user');
    }

    public function mitraPengguna()
    {
        return $this->morphedByMany(MitraPengguna::class, 'organisasiable', 'organisasi_user');
    }

    public function regulatorOrganisasi()
    {
        return $this->morphedByMany(RegulatorOrganisasi::class, 'organisasiable', 'organisasi_user');
    }

    public function apiKeys()
    {
        return $this->morphMany(ApiKey::class, 'owner');
    }
}
