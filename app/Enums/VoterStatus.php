<?php

namespace App\Enums;

enum VoterStatus: string
{
    case Eligible = 'eligible';
    case Ineligible = 'ineligible';
}
