<?php
namespace App\Enum;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case CONFIRMED = 'confirmed';
    case CANCELED = 'canceled';
    case COMPLETED = 'completed';
    
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}