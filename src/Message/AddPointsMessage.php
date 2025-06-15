<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
final class AddPointsMessage
{
}