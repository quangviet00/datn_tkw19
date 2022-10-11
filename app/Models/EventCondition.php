<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kyslik\ColumnSortable\Sortable;

/**
 * @property int $id
 * @property int $event_id
 * @property mixed $target_gender
 * @property bool $target_age_type
 * @property int $target_age_from
 * @property int $target_age_to
 * @property string $other_conditions
 * @property bool $limit_number_of_participants_flag
 * @property int $limit_number_of_participants
 * @property bool $participation_terms_type
 * @property string $special_notes
 * @property string $address
 * @property string $company_name
 * @property string $meet_condition
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
class EventCondition extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'integer';

    /**
     * @var array
     */
    protected $fillable = ['event_id', 'target_gender', 'target_age_type', 'target_age_from', 'target_age_to', 'other_conditions', 'limit_number_of_participants_flag', 'limit_number_of_participants', 'participation_terms_type', 'special_notes', 'address', 'company_name', 'meet_condition', 'created_at', 'updated_at', 'deleted_at'];

    protected $casts = [
        'target_gender' => 'array',
    ];

    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}