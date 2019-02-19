<?php

namespace Aacotroneo\Saml2\Models;

use Illuminate\Database\Eloquent\Model;
use Aacotroneo\Saml2\Exceptions\MessageExistsException;
use Aacotroneo\Saml2\Facades\Saml2;

class SamlMessage extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type',
        'slug',
        'message_id',
        'name_id',
        'session_index',
        'content',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get message name ID.
     *
     * @param string|null $value
     *
     * @return string|null
     */
    public function getNameIdAttribute(?string $value): ?string
    {
        if (! empty($value) && Saml2::config()->secure_message_content) {
            $value = decrypt($value);
        }

        return $value;
    }

    /**
     * Set message name ID.
     *
     * @param string|null $value
     *
     * @return void
     */
    public function setNameIdAttribute(?string $value): void
    {
        if (! empty($value) && Saml2::config()->secure_message_content) {
            $value = encrypt($value);
        }
        $this->attributes['name_id'] = $value;
    }

    /**
     * Get message content.
     *
     * @param array|null $value
     *
     * @return array|null
     */
    public function getContentAttribute(?array $value): ?array
    {
        if (! empty($value) && Saml2::config()->secure_message_content) {
            $value = decrypt($value);
        }

        return $value;
    }

    /**
     * Set message content.
     *
     * @param array|null $value
     *
     * @return void
     */
    public function setContentAttribute(?array $value): void
    {
        if (! empty($value) && Saml2::config()->secure_message_content) {
            $value = encrypt($value);
        }
        $this->attributes['content'] = $value;
    }

    /**
     * Whether a unique entity exists.
     *
     * @param string $slug       Service Provider slug.
     * @param string $message_id Unique message ID.
     *
     * @return bool
     */
    public static function uniqueExists(string $slug, string $message_id): bool
    {
        return static::where('slug', '=', $slug)->where('message_id', '=', $message_id)->exists();
    }

    /**
     * Check if unique entity exists and throw exception if so.
     *
     * @param string $slug       Service Provider slug.
     * @param string $message_id Unique message ID.
     *
     * @return void
     */
    public static function failOnUniqueExists(string $slug, string $message_id): void
    {
        if (static::uniqueExists($slug, $message_id)) {
            throw new MessageExistsException($slug, $message_id);
        }
    }
}
