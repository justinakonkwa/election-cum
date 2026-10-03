<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Active = 'active';
    case Withdrawn = 'withdrawn';
}
