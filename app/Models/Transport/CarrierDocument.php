<?php

namespace App\Models\File;

use Carbon\Carbon;

/**
 * Class File.
 *
 * @property int    $id
 * @property int    $machinery_id
 * @property int    $file_id
 * @property bool   $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $inactivated_at
 */
class CarrierDocument extends FileAttachment
{
    protected $table = 'transport.carrier_document';

    protected $fillable = [
        'machinery_id',
        'file_id',
        'active',
        'created_at',
        'updated_at',
        'inactivated_at',
    ];
}
