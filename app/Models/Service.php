<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="Service",
 *   @OA\Property(
 *     property="id",
 *     type="string",
 *     example="SAHAB_PARTAI_SPEACH_TO_TEXT"
 *   ),
 *   @OA\Property(
 *     property="name",
 *     type="string",
 *     example="آوانگار (تبدیل گفتار به متن) Speech To Text"
 *   ),
 *   @OA\Property(
 *     property="is_active",
 *     type="boolean",
 *     example="false"
 *   ),
 *   @OA\Property(
 *     property="has_credential",
 *     type="boolean",
 *     example="false"
 *   ),
 * )
 */
class Service extends Model
{
    /*=================================== Static Properties ====================================*/
    public static array $services = [
        [
            "id" => "SahabPartAISpeechToText",
            "name" => "آوانگار (تبدیل گفتار به متن) Speech To Text",
        ]
    ];

    public static array $serviceNames = [
        [
            "id" => "SahabPartAISpeechToText",
            "name" => "VoiceToText",
        ]
    ];

    private static string $servicesDirectory = "/uploads/services";

    /*=================================== Model Properties ====================================*/
    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'credential',
        'active'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'credential' => 'encrypted',
            'active' => 'boolean',
        ];
    }

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'is_active',
        'has_credential',
    ];

    /*=================================== Scopes ====================================*/
    /**
     * Scope a query to only include active services.
     */
    public function scopeActive(Builder $query): void
    {
        $query
            ->whereNotNull('credential')
            ->where('active', 1);
    }

    /*=================================== Model Attributes ====================================*/
    protected function credential(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => decrypt($value),
            set: fn (string $value) => encrypt($value),
        );
    }

    protected function hasCredential(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => !!$attributes["credential"],
        );
    }

    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => $attributes["active"] && $attributes["credential"],
        );
    }

    /*=================================== Relationship ====================================*/
    public function price(): HasOne
    {
        return $this->hasOne(ServicePrice::class, 'service_id');
    }

    /*=================================== Static Methods ====================================*/
    public static function getClassByShownName(string $name, bool $onlyId = false): self|string
    {
        $service = collect(self::$serviceNames)->where('name', $name)->first();
        throw_if(!$service, new InvalidArgumentException("wrong name specified"));
        if ($onlyId) return $service['id'];
        return self::query()->active()->find($service['id']);
    }
    /**
     * generate a separator that does not exist in the $username and $password
     * the Separator is in format of ==ps(a number)==
     */
    protected static function generateSeparatorForUsernamePassword(string $username, string $password): string
    {
        $separator = "==ps" . rand(0, 100) . "==";
        if (str_contains($username, $separator) || str_contains($password, $separator))
            return self::generateSeparatorForUsernamePassword($username, $password);
        else
            return $separator;
    }

    public static function concatUsernameAndPassword(string $username, string $password): string
    {
        $separator = self::generateSeparatorForUsernamePassword($username, $password);
        return $username . $separator . $password;
    }

    public static function splitUsernameAndPassword(string $hashed): array
    {
        $index = -1;
        $len = -1;
        if (preg_match("/==ps[0-9]+==/m", $hashed, $matches, PREG_OFFSET_CAPTURE)) {
            $len = strlen($matches[0][0]);
            $index = $matches[0][1];
        }

        if ($index < 0)
            throw new InvalidArgumentException("$hashed was not generated by this class", 422);

        $username = substr(string: $hashed, offset: 0, length: $index);
        $password = substr(string: $hashed, offset: $index + $len);

        return ["username" => $username, "password" => $password];
    }

    public static function getDirectoryPath()
    {
        if (!Storage::directoryExists(self::$servicesDirectory))
            Storage::makeDirectory(self::$servicesDirectory);

        return self::$servicesDirectory;
    }
}
