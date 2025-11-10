<?php

namespace Domain\Shared\Enums;

enum NotificationChannel: string
{
    case Email = 'email';
    case Wa = 'wa';
    case App = 'app';
}

