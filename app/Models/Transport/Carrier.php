<?php

namespace App\Models\Transport;

use App\Models\Hr\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Carrier.
 *
 * Represents a transport carrier with associated attributes and logic.
 *
 * @property int         $id
 * @property string      $uuid
 * @property int         $user_id
 * @property string      $name
 * @property string      $model
 * @property string      $plate
 * @property string      $renavam
 * @property string      $chassi
 * @property string      $manufacturer
 * @property int         $manufacture_year
 * @property string      $licensing_uf
 * @property string      $vehicle_type
 * @property string      $body_type
 * @property float       $plank_length
 * @property float       $tare
 * @property float       $pbtc
 * @property int         $axles
 * @property int         $tires_per_axle
 * @property null|string $obs
 * @property bool        $active
 * @property null|Carbon $inactivated_at
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
class Carrier extends Model
{
    use HasFactory;

    protected $table = 'transport.carrier';

    protected $fillable = [
        'uuid',
        'user_id',
        'name',
        'model',
        'plate',
        'renavam',
        'chassi',
        'manufacturer',
        'manufacture_year',
        'licensing_uf',
        'vehicle_type',
        'body_type',
        'plank_length',
        'tare',
        'pbtc',
        'axles',
        'tires_per_axle',
        'traction',
        'rntrc',
        'owner_document',
        'obs',
        'active',
        'inactivated_at',
    ];

    protected $attributes = [
        'active' => true,
    ];

    protected $casts = [
        'manufacture_year' => 'integer',
        'plank_length' => 'float',
        'tare' => 'float',
        'pbtc' => 'float',
        'axles' => 'integer',
        'tires_per_axle' => 'integer',
        'inactivated_at' => 'datetime',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'inactivated_at',
    ];

    /**
     * Relacionamento com o Usuário (transportador/solicitante).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relacionamento com as fotos do veículo.
     */
    public function pictures()
    {
        return $this->hasMany(CarrierPicture::class, 'carrier_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(CarrierDocument::class, 'carrier_id', 'id');
    }

    public function addPicture(string $id)
    {
        CarrierPicture::create([
            'carrier_id' => $this->id,
            'file_id' => $id,
        ]);
    }

    public function addDocument(string $id)
    {
        CarrierDocument::create([
            'carrier_id' => $this->id,
            'file_id' => $id,
        ]);
    }
}
