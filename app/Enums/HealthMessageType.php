<?php

namespace App\Enums;

enum HealthMessageType: string
{
    case Chat = 'chat';
    case Safety = 'safety';
    case Assessment = 'assessment';
}
