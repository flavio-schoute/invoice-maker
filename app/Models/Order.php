<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LaravelArchivable\Archivable;

class Order extends Model
{
    use HasFactory;
    use Archivable;
}
